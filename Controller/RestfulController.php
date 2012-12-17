<?php

namespace Brightmarch\Bundle\RestfulBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpNotAcceptableException;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpNotExtendedException;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpUnauthorizedException;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpUnsupportedMediaTypeException;

class RestfulController extends Controller
{
    
    /** @var array */
    private $supportedTypes = ['*/*'];

    /** @var array */
    private $requiredTypes = [];

    /** @var array */
    private $availableTypes = [];

    /** @var string */
    private $contentType = 'text/html';

    /** @var string */
    private $viewType = '';

    /** @var string */
    private $viewTemplateName = '';

    /** @var string */
    private $viewTemplate = '%s.%s.twig';

    /**
     * Set a list of content types that this resource supports.
     *
     * @param [string, string, ...]
     * @return this
     */
    public function resourceSupports()
    {
        $this->supportedTypes = array_merge(func_get_args(), $this->supportedTypes);
        $this->canClientAcceptThisResponse();

        return $this;
    }

    /**
     * Set a list of content types this resource requires.
     *
     * @param [string, string, ...]
     * @return this
     */
    public function resourceRequires()
    {
        $this->requiredTypes = array_merge(func_get_args(), $this->requiredTypes);
        $this->canServerSupportThisRequest();

        return $this;
    }
    
    /**
     * Creates a Response object and returns it.
     *
     * @param string $view
     * @param array $parameters
     * @param integer $statusCode
     * @return Response
     */
    public function renderResource($view, array $parameters=[], $statusCode=200)
    {
        $this->findContentType()
            ->findViewType()
            ->buildViewTemplate($view)
            ->checkViewTemplateExists();

        $response = $this->createResponse($statusCode);
        $response->headers
            ->set('Content-Type', $this->contentType);

        return $this->render($this->viewTemplateName, $parameters, $response);
    }
    
    public function renderCreatedResource($view, array $parameters=[])
    {
        return $this->renderResource($view, $parameters, 201);
    }

    public function renderAcceptedResource($view, array $parameters=[])
    {
        return $this->renderResource($view, $parameters, 202);
    }

    
    
    /**
     * Determines if the client can accept one of the media types the server supports.
     *
     * @return boolean
     */
    protected function canClientAcceptThisResponse()
    {
        $this->findAvailableTypes()
            ->checkAvailableTypes();

        return true;
    }

    /**
     * Determines if the server can support the media type the client has sent.
     *
     * @return boolean
     */
    protected function canServerSupportThisRequest()
    {
        $contentType = $this->getRequest()
            ->headers
            ->get('content-type');

        $contentType = strtolower($contentType);
        $contentType = trim(explode(';', $contentType)[0]);

        if (!in_array($contentType, $this->requiredTypes)) {
            throw new HttpUnsupportedMediaTypeException(sprintf("The media type %s is not supported by this resource.", $contentType));
        }

        return true;
    }
    
    protected function findAvailableTypes()
    {
        $this->availableTypes = array_intersect($this->getRequest()->getAcceptableContentTypes(), $this->supportedTypes);

        return $this;
    }
    
    protected function checkAvailableTypes()
    {
        if (0 === count($this->availableTypes)) {
            $this->throwNotAcceptableException();
        }

        return true;
    }
    
    protected function throwNotAcceptableException()
    {
        $message = "This resource can not respond with a format the client will find acceptable. %s";
        
        // Pop off the last element because it is always */* and a resource can not support it.
        $supportedTypes = $this->supportedTypes;
        array_pop($supportedTypes);
        
        if (0 === count($supportedTypes)) {
            $message = sprintf($message, "This resource has not defined any supported types.");
        } else {
            $message = sprintf($message, sprintf("This resource supports: [%s].", implode(', ', $supportedTypes)));
        }
        
        throw new HttpNotAcceptableException($message);
    }
    
    
    
    private function createResponse($statusCode)
    {
        $memoryUsage = round((memory_get_peak_usage() / 1048576), 4);
        
        $response = new Response;
        $response->setProtocolVersion('1.1');
        $response->setStatusCode($statusCode);
        $response->headers
            ->set('X-Men', $this->randomXPerson());
        $response->headers
            ->set('X-Memory-Usage', $memoryUsage);
        
        return $response;
    }
    
    private function findContentType()
    {
        $contentType = current($this->availableTypes);
        
        if ('*/*' != $contentType) {
            $this->contentType = $contentType;
        } elseif (count($this->supportedTypes) > 0) {
            $this->contentType = current($this->supportedTypes);
        }

        return $this;
    }
    
    private function findViewType()
    {
        $viewBits = explode('/', $this->contentType);
        
        $this->viewType = end($viewBits);
        $this->viewType = strtolower($this->viewType);

        return $this;
    }
    
    private function buildViewTemplate($view)
    {
        $this->viewTemplateName = sprintf($this->viewTemplate, $view, $this->viewType);

        return $this;
    }
    
    private function checkViewTemplateExists()
    {
        $templateExists = $this->container
            ->get('templating')
            ->exists($this->viewTemplateName);
            
        if (!$templateExists) {
            throw new HttpNotExtendedException(sprintf("The view %s does not exist. While this resource claims it can support this content type, it has no way to render it properly.", $this->viewTemplateName));
        }

        return $this;
    }

    private function randomXPerson()
    {
        $xpeople = array('Professor X', 'Cyclops', 'Jean Grey', 'Wolverine',
            'Storm', 'Rogue', 'Gambit', 'Jubilee', 'Beast', 'Morph', 'Iceman',
            'Polaris', 'Archangel', 'Angel', 'Colossus', 'Nightcrawler', 'Shadowcat',
            'Firestar', 'Thunderbird', 'Dazzler'
        );

        $xpeopleCount = count($xpeople)-1;
        $xpersonIdx = mt_rand(0, $xpeopleCount);

        return $xpeople[$xpersonIdx];
    }
    
}
