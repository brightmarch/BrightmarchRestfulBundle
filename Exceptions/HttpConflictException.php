<?php namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpConflictException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 409);
    }

}
