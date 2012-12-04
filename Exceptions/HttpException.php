<?php namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpException extends \Exception
{

    public $exception_message = "";
    public $http_code = 0;

    public function __construct($message="", $code=0)
    {
        parent::__construct($message, (int)$code);

        $this->exception_message = $message;
        $this->http_code = (int)$code;
    }

}
