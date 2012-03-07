<?php namespace Brightmarch\RestfulBundle\Exceptions;

class HttpNotExtendedException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 510);
    }

}
