<?php

namespace Brightmarch\Bundle\RestfulBundle\Entity;

abstract class Entity
{

    public function __construct()
    {
    }

    /**
     * Enable an entity.
     */
    public function enable()
    {
        $this->status = 1;
        return($this);
    }

    /**
     * Disable an entity.
     */
    public function disable()
    {
        $this->status = 0;
        return($this);
    }

    /**
     * Determine if an entity is enabled or not.
     */
    public function isEnabled()
    {
        return(1 == $this->status);
    }

    /**
     * Method meant to be overwritten to load specific parameters from an array.
     */
    public function loadFromParameters(array $parameters)
    {
        return($this);
    }

    /**
     * Grab a specific parameter and set it internally.
     */
    public function fetchParameter($key, array $parameters, $member)
    {
        $setter = $this->buildSetter($member);
        if (array_key_exists($key, $parameters) && method_exists($this, $setter)) {
            $this->$setter($parameters[$key]);
        }
        return($this);
    }

    /**
     * Returns the numeric value for an enabled entity.
     */
    public static function enabledFlag()
    {
        return(1);
    }

    /**
     * Returns the numeric value for a disabled entity.
     */
    public static function disabledFlag()
    {
        return(0);
    }


    /**
     * Turn a key like billing_fullname to BillingFullname and
     * then create a setter method like setBillingFullname.
     */
    private function buildSetter($key)
    {
        $key = str_replace('_', ' ', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '', $key);

        return(sprintf('set%s', $key));
    }

}
