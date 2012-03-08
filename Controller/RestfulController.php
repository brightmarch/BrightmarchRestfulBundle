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

        $response = new Response;
        $response->setProtocolVersion('1.1');
        $response->setStatusCode($status_code);
        $response->headers->set('Content-Type', $this->contentType);
        
        return($this->render($this->viewTemplateName, $parameters, $response));
    }
    
    public function renderCreatedResource($view, array $parameters=array())
    {
        return($this->renderResource($view, $parameters, 201));
    }
    
    public function renderException(\Exception $e)
    {
        $status_code = ((int)$e->getCode() > 0 ? (int)$e->getCode() : 500);
        
        // HTTP spec says we can render error messages in whatever
        // content we wish, so all error messages will be rendered in JSON.
        $response = new Response;
        $response->setProtocolVersion('1.1');
        $response->setStatusCode($status_code);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        
        // Give the user the chance to authenticate the request.
        if ($e instanceof HttpUnauthorizedException) {
            $response->headers->set('WWW-Authenticate', 'Basic realm=Accthub');
        }
        
        return($this->render('BrightmarchRestfulBundle:Restful:exception.json.twig',
            array('http_code' => $status_code, 'message' => $e->getMessage()),
            $response
        ));
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
    
    
    
    private function findContentType()
    {
        $content_type = current($this->availableTypes);
        if ('*/*' != $content_type) {
            $this->contentType = $content_type;
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
        if (!$this->container->get('templating')->exists($this->viewTemplateName)) {
            throw new HttpNotExtendedException(sprintf("The view %s does not exist. While this resource claims it can support this content type, it has no way to render it properly.", $this->viewTemplateName));
        }
        return($this);
    }

}
