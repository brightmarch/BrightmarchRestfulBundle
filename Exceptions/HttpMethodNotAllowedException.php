<?php namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpMethodNotAllowedException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 405);
    }

}
