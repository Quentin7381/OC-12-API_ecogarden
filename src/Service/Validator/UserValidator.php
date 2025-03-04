<?php

namespace App\Service\Validator;
use Exception;
use App\Entity\User;

class UserValidator extends Validator
{
    public function validate_username($value)
    {
        if(empty($value)){
            throw new Exception('Value cannot be empty');
        }

        if(!is_string($value)){
            throw new Exception('Invalid value format, requires a string');
        }

        if(strlen($value) < 3){
            throw new Exception('Value must be at least 3 characters long');
        }

        // If this is a new user
        $token = $this->getUser();
        if($token){
            $current_user = $token->getUser();
        } else {
            $current_user = null;
        }
        $updated_id = $this->request['additional']['user_id'] ?? null;
        if(
            !$current_user // We are creating a new user
            || !empty($updated_id) // We are updating an user
                && $current_user->getId() !== $updated_id // And it's not the current user
        ){
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $value]);
            if($existingUser){
                throw new Exception('Username already exists');
            }
        }
    }

    public function validate_postal_code(&$value)
    {
        if(empty($value)){
            throw new Exception('Value cannot be empty');
        }

        if(!is_string($value)){
            throw new Exception('Invalid value format, requires a string');
        }

        if(strlen($value) !== 5){
            throw new Exception('Value must be 5 exactly characters long');
        }

        if(!is_numeric($value)){
            throw new Exception('Value must be numeric');
        }

        if($value < 1000 || $value > 99999){
            throw new Exception('Value must be between 1000 and 99999');
        }
    }

    public function validate_password($value)
    {
        if(empty($value)){
            throw new Exception('Value cannot be empty');
        }

        if(!is_string($value)){
            throw new Exception('Invalid value format, requires a string');
        }

        if(strlen($value) < 8){
            throw new Exception('Value must be at least 8 characters long');
        }

        if(!preg_match('/[A-Z]/', $value)){
            throw new Exception('Value must contain at least one uppercase letter');
        }

        if(!preg_match('/[a-z]/', $value)){
            throw new Exception('Value must contain at least one lowercase letter');
        }

        if(!preg_match('/[0-9]/', $value)){
            throw new Exception('Value must contain at least one number');
        }

        if(!preg_match('/[^A-Za-z0-9]/', $value)){
            throw new Exception('Value must contain at least one special character');
        }
    }

    public function validate_roles($value)
    {
        $availableRoles = $this->roleHierarchy->getReachableRoleNames(['ROLE_ADMIN']);

        if(empty($value)){
            $value = ['ROLE_USER'];
        } else {
            // The user must be admin to assign roles other than 'ROLE_USER'
            $user = $this->getUser();
            if(
                !$user
                || !in_array('ROLE_ADMIN', $user->getRoles())
            ){
                throw new Exception('Only administrators can assign roles');
            }

            if(!is_array($value)){
                throw new Exception('Invalid value format, requires an array');
            }
    
            foreach($value as $role){
                if(!is_string($role)){
                    throw new Exception('Invalid value format, requires an array of strings');
                }
    
                if(!in_array($role, $availableRoles)){
                    throw new Exception('Invalid role. Available roles are: ' . implode(', ', $availableRoles));
                }
            }

        }
    }
}