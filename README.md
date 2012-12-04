# Symfony2 RESTful Bundle
This is a very small but powerful Symfony2 bundle for quickly building RESTful resources (specifically, HTTP APIs).

## Installation
Installation is relatively easy. It requires three steps. Start by adding the right dependency to your `composer.json` file and install the new bundle.

    "brightmarch/restful-bundle": "*"

You can safely assume that what is in `master` is always up to date.

    $ composer.phar install brightmarch/restful-bundle

Once installed, add it to `app/AppKernel.php` in the main `$bundles` array. You do not want this to be part of the dev system only.

    $bundles = array(
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ... other bundles ... //
        new Brightmarch\Bundle\RestfulBundle\BrightmarchRestfulBundle(),
    );

Installation is complete, you are now ready to begin building a RESTful resource.

## Usage
To start, you will most likely want to start with your own bundle. Each resource that you want to work with this bundle must extend the `Brightmarch\Bundle\RestfulBundle\Controller\RestfulController` controller.

### Sample Controller
    <?php
    
    namespace Brightmarch\ResourceBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use Brightmarch\Bundle\RestfulBundle\Controller\RestfulController;
    use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpUnauthorizedException;

    class ResourceController extends RestfulController
    {

        // A typical Symfony2 action
        public function indexAction()
        {
            // Describe what content types this resource supports.
            $this->resourceSupports('application/json', 'application/xml', 'text/html');
            
            // Render a view. The type of the view will automatically be found based on the Accept header.
            return $this->renderResource('BrightmarchResourceBundle:Resource:index', ['message' => 'Welcome to my RESTful resource!']));
        }

    }

You must describe what content types this resource supports. This means if a client sends an Accept header with a content type this resource does not accept, a 406 Unacceptable response will be returned. Because this resource supports three content types, you must have three different views: index.json.twig, index.xml.twig, and index.html.twig.

### index.json.twig
    {% autoescape false %}
    {
        "message": {{ message|json_encode }}
    }
    {% endautoescape %}

### index.xml.twig
    <?xml version="1.0" encoding="UTF-8"?>
    <accthub>
        <message>{{ message }}</message>
    </accthub>

### index.html.twig
    <!doctype html>
    <html>
    <head>
        <title>{{ message }}</title>
    </head>
    <body>
        <p>{{ message }}</p>
    </body>
    </html>

A slightly more advanced controller might find an entity and render it. You want to render it in the format requested. You would simply create the templates for those formats. Don't forget that resources should link to one another and each other, if necessary. Here is a sample template for a Client in Accthub.

    {% autoescape false %}
    {
        "id": {{ client.id }},
        "created": {{ client.created.date|json_encode }},
        "updated": {{ client.updated|json_encode }},
        "name": {{ client.name|json_encode }},
        "identifier": {{ client.identifier|json_encode }},
        "email": {{ client.identifier|json_encode }},
        "lang": {{ client.lang|json_encode }},
        "_links": [
            {
                "rel": "self",
                "url": {{ url("BrightmarchAccthubApiBundle_clients_read_by_id", {"id": client.id})|json_encode }}
            },
            {
                "rel": "alt",
                "url": {{ url("BrightmarchAccthubApiBundle_clients_read_by_identifier", {"identifier": client.identifier})|json_encode }}
            }
        ]
    }
    {% endautoescape %}

You will notice there are two `_links` records. One points directly back to itself, and the other provides an alternative URL to the same resource.

## Errors
This bundle supports handling HTTP errors properly. It comes with several exceptions for handling errors. They include:

* 400: `HttpBadSyntaxException`
* 405: `HttpMethodNotAllowedException`
* 406: `HttpNotAcceptableException`
* 409: `HttpConflictException`
* 415: `HttpUnsupportedMediaTypeException`
* 510: `HttpNotExtendedException`
* 404: `HttpNotFoundException`
* 401: `HttpUnauthorizedException`

You are responsible for rendering your errors. There is a default template located in `Resources/views/Restful/error.json.twig`. I suggest reading about catching kernel exceptions in Symfony for how to catch and return HTTP REST errors. The error response template looks like this:

    {% autoescape false %}
    {
        "httpCode": {{ httpCode }},
        "message": {{ message|json_encode }}
    }
    {% endautoescape %}

The HTTP code will also be sent back as part of the response payload. For example, making this request to the resource above would result in a 406 error:

    $ curl -v -H "Accept: invalid/type" http://example.com/

    < HTTP/1.1 406 Not Acceptable
    < Content-Length: 195
    < Content-Type: application/json; charset=utf-8
    < 
    {
        "httpCode": 406,
        "message": "This resource can not respond with a format the client will find acceptable. This resource supports: [application\/json, application\/xml, text\/html]."
    }

If the error code of an exception can not be determine, the default 500 error code is used. I will be adding more `HttpException` classes as I need them.

## Credits
Originally written by Vic Cherubini for Brightmarch, LLC. This library is MIT licensed. Copyright 2012.
