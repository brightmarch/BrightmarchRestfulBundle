<?php

namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpException extends \Exception
{

    public $exceptionMessage = "";
    public $httpCode = 0;

    public function __construct($message="", $code=0)
    {
        parent::__construct($message, (int)$code);

        $this->exceptionMessage = $message;
        $this->httpCode = (int)$code;
    }

}
