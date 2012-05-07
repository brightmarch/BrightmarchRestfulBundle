<?php

namespace Brightmarch\Bundle\RestfulBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpNotAcceptableException;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpNotExtendedException;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpUnauthorizedException;

class RestfulController extends Controller
{
    
    private $supportedTypes = array('*/*');
    private $availableTypes = array();
    
    private $contentType = 'text/html';
    private $viewType = '';
    private $viewTemplateName = '';
    private $viewTemplate = '%s.%s.twig';
    
    public function resourceSupports()
    {
        $this->supportedTypes = array_merge(func_get_args(), $this->supportedTypes);
        return($this);
    }
    
    public function renderResource($view, array $parameters=array(), $status_code=200)
    {
        $this->canClientAcceptThisResponse();
        $this->findContentType()
            ->findViewType()
            ->buildViewTemplate($view)
            ->checkViewTemplateExists();

        $response = $this->createResponse($status_code);
        $response->headers->set('Content-Type', $this->contentType);
        
        return($this->render($this->viewTemplateName, $parameters, $response));
    }
    
    public function renderCreatedResource($view, array $parameters=array())
    {
        return($this->renderResource($view, $parameters, 201));
    }
    
    public function renderException(\Exception $e)
    {
        $status_code = ((int)$e->getCode() > 0 && (int)$e->getCode() < 600 ? (int)$e->getCode() : 500);
        
        // HTTP spec says we can render error messages in whatever
        // content we wish, so all error messages will be rendered in JSON.
        $response = $this->createResponse($status_code);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        
        // Give the user the chance to authenticate the request.
        if ($e instanceof HttpUnauthorizedException) {
            $response->headers->set('WWW-Authenticate', 'Basic realm=Secure Website');
        }
        
        return($this->render('BrightmarchRestfulBundle:Restful:exception.json.twig',
            array('http_code' => $status_code, 'message' => $e->getMessage()),
            $response
        ));
    }

    public function isResourceValid($resource)
    {
        if (!is_object($resource)) {
            return(false);
        }

        $errors = $this->get('validator')
            ->validate($resource);
        return(0 === count($errors));
    }

    public function hasParametersParameter($p, $k)
    {
        $parameters = $this->getParameters($p);
        return(array_key_exists($k, $parameters));
    }

    public function getParameters($key)
    {
        $parameters = $this->getRequest()
            ->request
            ->get($key, array());

        return($parameters);
    }

    public function getParametersParameter($p, $k)
    {
        $parameters = $this->getParameters($p);
        if (array_key_exists($k, $parameters)) {
            return($parameters[$k]);
        }
        return(null);
    }

    
    
    protected function canClientAcceptThisResponse()
    {
        $this->findAvailableTypes()
            ->checkAvailableTypes();
        return(true);
    }
    
    protected function findAvailableTypes()
    {
        $this->availableTypes = array_intersect($this->getRequest()->getAcceptableContentTypes(), $this->supportedTypes);
        return($this);
    }
    
    protected function checkAvailableTypes()
    {
        if (0 === count($this->availableTypes)) {
            $this->throwNotAcceptableException();
        }
        return(true);
    }
    
    protected function throwNotAcceptableException()
    {
        $message = "This resource can not respond with a format the client will find acceptable. %s";
        
        // Pop off the last element because it is always */* and a resource can not support it.
        $supported_types = $this->supportedTypes;
        array_pop($supported_types);
        
        if (0 == count($supported_types)) {
            $message = sprintf($message, "This resource has not defined any supported types.");
        } else {
            $message = sprintf($message, sprintf("This resource supports: [%s].", implode(', ', $supported_types)));
        }
        
        throw new HttpNotAcceptableException($message);
    }
    
    
    
    private function createResponse($status_code)
    {
        $memory_usage = round((memory_get_peak_usage() / 1048576), 4);
        
        $response = new Response;
        $response->setProtocolVersion('1.1');
        $response->setStatusCode($status_code);
        $response->headers->set('X-Men', $this->randomXPerson());
        $response->headers->set('X-Memory-Usage', $memory_usage);
        
        return($response);
    }
    
    private function findContentType()
    {
        $content_type = current($this->availableTypes);
        if ('*/*' != $content_type) {
            $this->contentType = $content_type;
        } elseif (count($this->supportedTypes) > 0) {
            $this->contentType = current($this->supportedTypes);
        }

        return($this);
    }
    
    private function findViewType()
    {
        $view_bits = explode('/', $this->contentType);
        
        $this->viewType = end($view_bits);
        $this->viewType = strtolower($this->viewType);
        return($this);
    }
    
    private function buildViewTemplate($view)
    {
        $this->viewTemplateName = sprintf($this->viewTemplate, $view, $this->viewType);
        return($this);
    }
    
    private function checkViewTemplateExists()
    {
        $template_exists = $this->container
            ->get('templating')
            ->exists($this->viewTemplateName);
            
        if (!$template_exists) {
            throw new HttpNotExtendedException(sprintf("The view %s does not exist. While this resource claims it can support this content type, it has no way to render it properly.", $this->viewTemplateName));
        }
        return($this);
    }

    private function randomXPerson()
    {
        $xpeople = array('Professor X', 'Cyclops', 'Jean Grey', 'Wolverine',
            'Storm', 'Rogue', 'Gambit', 'Jubilee', 'Beast', 'Morph', 'Iceman',
            'Polaris', 'Archangel', 'Angel', 'Colossus', 'Nightcrawler', 'Shadowcat',
            'Firestar', 'Thunderbird', 'Dazzler'
        );
        $xpeople_count = count($xpeople)-1;
        $xperson_idx = mt_rand(0, $xpeople_count);
        
        return($xpeople[$xperson_idx]);
    }
    
}
