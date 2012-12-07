<?php

namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

use Symfony\Component\Validator\ConstraintViolationList;

class HttpException extends \Exception
{

    /** @var string */
    public $exceptionMessage = "";

    /** @var integer */
    public $httpCode = 0;

    /** @var array */
    public $violations = [];

    public function __construct($message="", $code=0)
    {
        parent::__construct($message, (int)$code);

        $this->exceptionMessage = $message;
        $this->httpCode = (int)$code;
    }
    
    public function setViolations(ConstraintViolationList $violations)
    {
        $this->violations = [];

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $this->violations[$propertyPath] = $violation->getMessage();
        }

        return $this;
    }

    public function getViolations()
    {
        return $this->violations;
    }

}