<?php

namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpPreconditionFailedException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 412);
    }

}