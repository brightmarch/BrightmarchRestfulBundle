<?php

namespace Brightmarch\Bundle\RestfulBundle\Exceptions;

class HttpPreconditionFailed extends HttpException 
{

    public function __construct($message)
    {
        parent::__construct($message, 412);
    }

}