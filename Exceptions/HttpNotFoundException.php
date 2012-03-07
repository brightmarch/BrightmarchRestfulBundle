<?php namespace Brightmarch\RestfulBundle\Exceptions;

class HttpNotFoundException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 404);
    }

}
