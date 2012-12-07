<?php

namespace Brightmarch\Bundle\RestfulBundle\Controller\Mixins;

use Brightmarch\Bundle\RestfulBundle\Entity\Entity;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpBadSyntaxException;

use Symfony\Component\Validator\ConstraintViolationList;

trait EntityValidationMixin
{

    /**
     * Tests the entity to see if there are any violations. If so, a HttpBadSyntaxException is thrown.
     *
     * @param Brightmarch\Bundle\RestfulBundle\Entity\Entity $entity
     * @param string $message
     * @return boolean
     */
    public function checkForViolations(Entity $entity, $message="")
    {
        $violations = $this->get('validator')
            ->validate($entity);

        if (count($violations) > 0) {
            $this->throwHttpBadSyntaxException($violations, $message);
        }

        return true;
    }



    private function throwHttpBadSyntaxException(ConstraintViolationList $violations, $message)
    {
        $exception = (new HttpBadSyntaxException($message))
            ->setViolations($violations);

        throw $exception;
    }

}
