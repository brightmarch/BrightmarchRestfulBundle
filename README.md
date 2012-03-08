# Symfony2 RESTful Bundle
This is a very small but powerful Symfony2 bundle for quickly building RESTful resources (specifically, HTTP API's).

## Installation
Installation is relatively easy. It requires three steps. Start by adding the right dependency to your `deps` file and install the new bundle.

    [BrightmarchRestfulBundle]
    git=http://github.com/brightmarch/BrightmarchRestfulBundle.git
    target=/bundles/Brightmarch/Bundle/RestfulBundle

You can safely assume that what is in `master` is always up to date.

    $ php bin/vendors update

Once installed, add it to `app/AppKernel.php` in the main `$bundles` array. You do not want this to be part of the dev system only.

    $bundles = array(
        new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        // ... other bundles ... //
        new Brightmarch\Bundle\RestfulBundle\BrightmarchRestfulBundle(),
    );

Finally, add `Brightmarch` to the `app/autoload.php` file so the bundle is loaded correctly.

    $loader->registerNamespaces(array(
        'Symfony'     => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
        // ... other namespaces ... //
        'Brightmarch' => __DIR__.'/../vendor/bundles',
    ));

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
        
            // Exceptions are used heavily, and to the best of my knowledge, Symfony2
            // has no way to automatically catch them in a controller, so you must
            // do that yourself.
            try {
            
                // Describe what content types this resource supports.
                $this->resourceSupports('application/json', 'application/xml', 'text/html');
                
                // Render a view. The type of the view will automatically be found based on the Accept header.
                return($this->renderResource('BrightmarchResourceBundle:Resource:index',
                    array('message' => 'Welcome to my RESTful resource!')));

            } catch (\Exception $e) {
                return($this->renderException($e));
            }
        
        }

    }

You must describe what content types this resource supports. This means if a client sends an Accept header with a content type this resource does not accept, a 406 Unacceptable response will be returned. Because this resource supports three content types, you must have three different views: index.json.twig, index.xml.twig, and index.html.twig.

### `index.json.twig`
    {"message": {{ message|json_encode|raw }}}

### `index.xml.twig`
    <?xml version="1.0" encoding="UTF-8"?>
    <accthub>
        <message>{{ message }}</message>
    </accthub>

### `index.html.twig`
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

* HttpBadSyntaxException, HTTP Status Code: 400
* HttpNotAcceptableException, HTTP Status Code: 406
* HttpNotExtendedException, HTTP Status Code: 510
* HttpNotFoundException, HTTP Status Code: 404
* HttpUnauthorizedException, HTTP Status Code: 401

The `catch` in the `indexAction` above will automatically catch these errors and render them. The HTTP spec specifies that error responses do not have to correspond with an Accept header. Thus, all error responses are given in JSON. The error response template looks like this:

    {% autoescape false %}
    {
        "http_code": {{ http_code }},
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
        "http_code": 406,
        "message": "This resource can not respond with a format the client will find acceptable. This resource supports: [application\/json, application\/xml, text\/html]."
    }

If the error code of an exception can not be determine, the default 500 error code is used. I will be adding more `HttpException` classes as I need them.

## Credits
Originally written by Vic Cherubini for Brightmarch, LLC. This library is MIT licensed. Copyright 2012.