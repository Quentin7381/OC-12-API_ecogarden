<?php

namespace App\Service\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class Validator {

    protected $request_data;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ContainerInterface $container,
        protected TokenStorageInterface $tokenStorage,
        // inject authorisation checker service
        protected AuthorizationCheckerInterface $authChecker,
        protected RoleHierarchyInterface $roleHierarchy
    ){
    }

    public function fill($request, $needs, $entity, $additional_data = []): array{
        $data = $this->validate($request, $needs, $additional_data);

        $collectedData = [];
        foreach($needs as $key => $value){
            foreach($value as $field => $validator){
                $field = trim($field, '?');
                $collectedData[$field] = $data[$key][$field] ?? null;
            }
        }

        foreach($collectedData as $field => $value){
            if($value === null){
                continue;
            }

            $setter = 'set' . ucfirst($field);
            $setter = preg_replace_callback('/_([a-z])/', function($matches){
                return strtoupper($matches[1]);
            }, $setter);

            $entity->$setter($value);
        }

        return $data;
    }

    public function validate($request, $needs, $additional_data = []): array{
        // We don't need to validate anything if there is no needs.
        if(empty($needs)){
            return [];
        }

        // Prepare the needs.
        $needs = $this->prepareNeeds($needs);

        // Prepare data
        $data = [
            'additional_data' => $additional_data,
            'header' => [],
            'query' => [],
            'body' => []
        ];

        if($request) {
            // Prepare the data.
            $data['query'] = $request->query->all();
            $data['header'] = $request->headers->all();
    
            // Get the body
            $body = $request->getContent();

            if(!empty($body)){
                $body = json_decode($body, true);
    
                // If the body is not a valid JSON, we throw an exception.
                if(empty($body)){
                    throw new HttpException(400, 'Invalid JSON body');
                }
            } else {
                $body = [];
            }

            $data['body'] = $body;
        }


        // Save the request data.
        $this->request_data = $data;

        // Validate the data.
        foreach($needs as $key => $value){
            foreach($value as $field => $validator){
                $value = &$data[$key][$field] ?? null;

                try {
                    $validator($value);
                } catch(Exception|HttpException $e){
                    $position = "$key.$field";
                    $message = $e->getMessage();
                    $message = "Validation failed for $position: $message";
                    throw new HttpException(400, $message);
                }
            }
        }

        return $data;
    }

    /**
     * Needs is an array of three keys, header, body, and query.
     * These sub-arrays have the same format : 'field' => 'validator'
     * 
     * @param mixed $needs
     * @return array
     */
    private function prepareNeeds($needs): array{
        // Needs is an array of four keys, header, body, query, and additional_data.
        $VALID_KEYS = ['header', 'body', 'query', 'additional_data'];

        // If none of the keys are set, we assume body.
        if(!array_intersect_key($needs, array_flip($VALID_KEYS))){
            $needs = ['body' => $needs];
        }

        // Once done, there is no other keys than header, body, and query.
        if(array_diff_key($needs, array_flip($VALID_KEYS))){
            throw new Exception('Invalid needs array keys, requires header, body, and/or query');
        }

        // These sub-arrays have the same format : 'field' => 'validator'
        foreach($needs as $key => $value){
            if(!is_array($value)){
                throw new Exception('Invalid needs array format, requires an array of fields and validators like so : ["body" => ["username" => "user::name"]]');
            }
        }

        // Prepare the validator.
        foreach($needs as &$value){
            foreach($value as &$validator){
                $validator = $this->prepareValidator($validator);
            }
        }

        return $needs;
    }

    private function prepareValidator($validator): callable{
        if(!is_string($validator)){
            throw new Exception('Invalid validator format, requires a string');
        }

        // If validator starts with '?' we assume it's optional.
        $optional = str_starts_with($validator, '?');

        // We remove the '?' if it was there.
        $validator = ltrim($validator, '?');

        // Validator is a string with the format 'class::field'
        [$class, $field] = explode('::', $validator);

        // If the format was only 'field', we assume the class is Validator.
        if(!$field) {
            $field = $class;
            $class = 'Validator';
        }

        // Class starts with a capital letter.
        $class = ucfirst($class);

        // Class ends with 'Validator'.
        if(!str_ends_with($class, 'Validator')){
            $class .= 'Validator';
        }

        // Class is in the Validator namespace.
        $class = 'App\Service\Validator\\' . $class;

        // Class exists.
        if(!class_exists($class)){
            throw new Exception('Validator class does not exist');
        }

        // Class is a subclass of Validator.
        if(!is_subclass_of($class, 'App\Service\Validator\Validator')){
            throw new Exception('Validator class is not a subclass of Validator');
        }

        // 'validate_field' is a method of the class.
        if(!method_exists($class, 'validate_' . $field)){
            throw new Exception("Method validate_$field does not exist in $class");
        }

        $validatorInstance = $this->container->get($class);
        $validator = \Closure::fromCallable([$validatorInstance, 'validate_' . $field]);

        if($optional){
            return function($value) use ($validator){
                if($value === null){
                    return;
                }

                $validator($value);
            };
        } else {
            return $validator;
        }
    }

    protected function validate_string(&$value){
        // The value is a string
        if(!is_string($value)){
            throw new Exception("The value must be a string");
        }

        // Title does not contain leading or trailing spaces
        $value = trim($value);
    }

    protected function getUser(){
        $token = $this->tokenStorage->getToken();
        if(!$token){
            return null;
        }

        return $token->getUser();
    }
}