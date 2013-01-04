<?php

namespace Brightmarch\Bundle\RestfulBundle\Controller\Mixin;

use Brightmarch\Bundle\RestfulBundle\Entity\Entity;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpBadSyntaxException;

use Symfony\Component\Validator\ConstraintViolationList;

trait EntityValidationMixin
{

    /**
     * Tests the entity to see if there are any violations.
     * If so, a HttpBadSyntaxException is thrown.
     *
     * @param Brightmarch\Bundle\RestfulBundle\Entity\Entity $entity
     * @param string $message
     * @return boolean
     */
    protected function _checkForEntityAssertions(Entity $entity, $message="")
    {
        $violations = $this->get('validator')
            ->validate($entity);

        if (count($violations) > 0) {
            $this->_throwHttpBadSyntaxException($violations, $message);
        }

        return true;
    }



    private function _throwHttpBadSyntaxException(ConstraintViolationList $violations, $message)
    {
        $exception = (new HttpBadSyntaxException($message))
            ->setViolations($violations);

        throw $exception;
    }

}
