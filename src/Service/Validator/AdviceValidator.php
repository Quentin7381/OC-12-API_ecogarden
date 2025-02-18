<?php

namespace App\Service\Validator;
use Exception;
use App\Entity\User;

class AdviceValidator extends Validator
{
    public function validate_month(&$value)
    {
        // Month is required
        if (empty($value)) {
            throw new Exception("The value is required");
        }

        // Month is an array
        if (!is_array($value)) {
            $value = [$value];
        }

        // Each month is a number
        foreach ($value as $v) {
            if (!is_numeric($v)) {
                throw new Exception("The value must be an array of numbers");
            }
        }

        // This number has no decimal
        foreach ($value as $v) {
            if ($v != intval($v)) {
                throw new Exception("The value must be an array of integers");
            }
        }
        
        $value = array_map('intval', $value);

        // Each number is between 1 and 12
        foreach ($value as $v) {
            if ($v < 1 || $v > 12) {
                throw new Exception("The value must be an array of integers between 1 and 12");
            }
        }

        // Keys are not important
        $value = array_values($value);
    }

    public function validate_title(&$value)
    {
        // Title is required
        if (empty($value)) {
            throw new Exception("The value is required");
        }

        // We apply general string validation
        $this->validate_string($value);

        // Title starts with an uppercase letter
        $value = ucfirst($value);

        // Title is at least 5 characters long
        if (strlen($value) < 5) {
            throw new Exception("The value must be at least 5 characters long");
        }

        // Title is at most 255 characters long
        if (strlen($value) > 255) {
            throw new Exception("The value must be at most 255 characters long");
        }
    }

    public function validate_content(&$value)
    {
        // Content is required
        if (empty($value)) {
            throw new Exception("The value is required");
        }

        // We apply general string validation
        $this->validate_string($value);

        // Content is at least 50 characters long
        if (strlen($value) < 50) {
            throw new Exception("The value must be at least 50 characters long");
        }

        // Content is at most 5000 characters long
        if (strlen($value) > 5000) {
            throw new Exception("The value must be at most 5000 characters long");
        }
    }

    public function validate_author(&$value)
    {
        // We load the current user
        $user = $this->getUser();
        $user_id = $user->getId();

        // If the author is not provided, we use the current user
        if (empty($value)) {
            $value = $user_id;
        }

        // Author is an id
        if (!is_numeric($value)) {
            throw new Exception("The value must be an integer");
        }

        // Author is a positive integer
        if ($value != intval($value) || $value < 1) {
            throw new Exception("The value must be a positive integer");
        }

        $value = intval($value);

        // We want an existing user
        $value = $this->entityManager->getRepository(User::class)->find($value);

        if (!$value) {
            throw new Exception("The value must be an existing user id. Your id is $user_id");
        }

        // Non admin users have more limited rights
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            if ($value->getId() != $user_id) {
                throw new Exception("You can only create advices for yourself. Your id is $user_id");
            }
        }
    }
}