<?php

namespace Brightmarch\Bundle\RestfulBundle\Controller\Mixins;


trait HttpAuthorizationTrait
{

    /** @var array */
    private $authorizationHeader = [];

    /** @var string */
    private $authorizationHeaderRaw = '';

    /**
     * Parses the Authorization header for authenticating the current request.
     *
     * @return boolean
     */
    private function parseAuthorizationHeader()
    {
        $this->findAuthorizationHeader()
            ->splitAuthorizationHeader();

        return true;
    }

    /**
     * Grabs the raw Authorization header to find the username and password.
     *
     * @return this
     */
    private function findAuthorizationHeader()
    {
        $this->authorizationHeaderRaw = $this->getRequest()
            ->headers
            ->get('authorization');

        // Strip off the Basic: prefix.
        $this->authorizationHeaderRaw = substr($this->authorizationHeaderRaw, 6);

        return $this;
    }

    /**
     * Splits the actual Authorization header into it's username and apiKey parts.
     *
     * @return this
     */
    private function splitAuthorizationHeader()
    {
        $username = $apiKey = '';
        
        $authorizationHeader = explode(':', base64_decode($this->authorizationHeaderRaw));
        if (2 == count($authorizationHeader)) {
            $username = strtolower($authorizationHeader[0]);
            $apiKey = $authorizationHeader[1];
        }

        $this->authorizationHeader = ['username' => $username, 'apiKey' => $apiKey];

        return $this;
    }

    /**
     * Returns the value of an Authorization header bit.
     * Keys: [username, apiKey]
     *
     * @return string
     */
    private function getAuthorizationHeaderKey($key)
    {
        $authorizationHeaderValue = null;
        if (array_key_exists($key, $this->authorizationHeader)) {
            $authorizationHeaderValue = $this->authorizationHeader[$key];
        }

        return $authorizationHeaderValue;
    }

}
