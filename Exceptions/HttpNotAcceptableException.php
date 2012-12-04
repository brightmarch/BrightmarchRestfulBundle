<?php namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpNotAcceptableException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 406);
    }

}
