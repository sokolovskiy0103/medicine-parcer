<?php

namespace App\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->errors = [];
        foreach ($violations as $violation) {
            $this->errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        parent::__construct();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}