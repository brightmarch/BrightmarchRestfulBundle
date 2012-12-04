<?php

namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpUnsupportedMediaTypeException extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 415);
    }

}
