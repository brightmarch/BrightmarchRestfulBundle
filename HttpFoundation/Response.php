<?php

namespace Brightmarch\Bundle\RestfulBundle\HttpFoundation;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{

    protected $internalPayload = null;

    public function setInternalPayload($internalPayload)
    {
        $this->internalPayload = $internalPayload;

        return($this);
    }

    public function getInternalPayload()
    {
        return($this->internalPayload);
    }

}
