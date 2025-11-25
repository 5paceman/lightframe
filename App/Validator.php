<?php

namespace App;

class ValidationException extends \Exception {}

class Validator {
    protected static $handlers = [
        'required' => function($field, $value, $params) {
            if(empty($value))
                throw new ValidationException("Field $field is required");
        },
        'string' => function($field, $value, $params) {
            if(!is_string($value))
                throw new ValidationException("Field $field must be a string");
        },
        'email' => function($field, $value, $params) {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL))
                throw new ValidationException("Field $field must be an email");
        },
        'max-char' => function($field, $value, $params): void { 
            if(strlen($value) > (int)$params)
                throw new ValidationException("Field $field must not exceed $params characters");
        },
        'min-char' => function($field, $value, $params): void { 
            if(strlen($value) < (int)$params)
                throw new ValidationException("Field $field must exceed $params characters");
        },
        'max' => function($field, $value, $params): void { 
            if($value > (int)$params)
                throw new ValidationException("Field $field must not exceed $params");
        },
        'min' => function($field, $value, $params): void { 
            if($value < (int)$params)
                throw new ValidationException("Field $field must be minimum of $params");
        },
        'numeric' => function($field, $value, $params) {
            if(!is_numeric($value))
                throw new ValidationException("Field $field must be a number");
        }

    ];

    public static function validate(array $data, array $rules)
    {
        foreach($rules as $field => $fieldRules)
        {
            foreach($fieldRules as $rule)
            {
                $params = null;

                if(strpos($rule, ':') !== false) {
                    [$ruleName, $params] = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                }

                if(!isset(self::$handlers[$ruleName])) {
                    throw new \Exception("Validation rule $ruleName not supported");
                }

                $verificationFunc = self::$handlers[$ruleName];
                $verificationFunc($field, $data[$field] ?? null, $params);
            }
        }
    }
}

?>