<?php

namespace Brightmarch\Bundle\RestfulBundle\Entity;

abstract class Entity
{

    public function __construct()
    {
    }

    /**
     * Enable an entity.
     *
     * @return this
     */
    public function enable()
    {
        $this->status = 1;

        return($this);
    }

    /**
     * Disable an entity.
     *
     * @return this
     */
    public function disable()
    {
        $this->status = 0;

        return($this);
    }

    /**
     * Determine if an entity is persisted to the datastore or not.
     *
     * @return boolean
     */
    public function isPersisted()
    {
        return($this->id > 0);
    }

    /**
     * Determine if an entity is enabled or not.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return(1 == $this->status);
    }

    /**
     * Method meant to be overwritten to load specific parameters from an array.
     *
     * @return this
     */
    public function hydrate(array $parameters)
    {
        return($this);
    }

    /**
     * Grab a specific parameter and set it internally.
     *
     * @param string The key in the parameters to fetch.
     * @param array The key-value array of parameters.
     * @return this
     */
    public function fetch($key, array $parameters)
    {
        $setter = $this->buildSetter($key);
        if (array_key_exists($key, $parameters) && method_exists($this, $setter)) {
            $this->$setter($parameters[$key]);
        }

        return($this);
    }

    /**
     * Returns the numeric value for an enabled entity.
     *
     * @return integer
     */
    public static function enabledFlag()
    {
        return(1);
    }

    /**
     * Returns the numeric value for a disabled entity.
     *
     * @return integer
     */
    public static function disabledFlag()
    {
        return(0);
    }



    /**
     * Turn a key like billing_fullname to BillingFullname and
     * then create a setter method like setBillingFullname.
     *
     * @return string
     */
    private function buildSetter($key)
    {
        $key = str_replace('_', ' ', $key);
        $key = ucwords($key);
        $key = str_replace(' ', '', $key);

        return(sprintf('set%s', $key));
    }

}
