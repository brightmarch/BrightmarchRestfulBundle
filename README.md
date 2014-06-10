# Symfony2 RESTful Bundle
This is a very small but powerful Symfony2 bundle for quickly building RESTful resources (specifically, HTTP APIs).

## Installation
Installation is relatively easy. It requires three steps. Start by adding the right dependency to your `composer.json` file and install the new bundle.

```javascript
"brightmarch/restful-bundle": "1.0.0"
```

You can safely assume that what is in `master` is always up to date.

```bash
$ composer.phar install brightmarch/restful-bundle
```

Once installed, add it to `app/AppKernel.php` in the main `$bundles` array. You do not want this to be part of the dev system only.

```php
$bundles = array(
    new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    // ... other bundles ... //
    new Brightmarch\Bundle\RestfulBundle\BrightmarchRestfulBundle(),
);
```

Installation is complete, you are now ready to begin building a RESTful resource.

## Usage
To start, you will most likely want to start with your own bundle. Each resource that you want to work with this bundle must extend the `Brightmarch\Bundle\RestfulBundle\Controller\RestfulController` controller.

### Sample Controller
```php
<?php

namespace Sample\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Brightmarch\Bundle\RestfulBundle\Controller\RestfulController;
use Brightmarch\Bundle\RestfulBundle\Exceptions\HttpUnauthorizedException;

class AccountsController extends RestfulController
{

    public function indexAction()
    {
        // Describe what content types this resource supports.
        $this->resourceSupports('application/json', 'application/xml', 'text/html');

        $accounts = $this->get('doctrine')
            ->getManager()
            ->getRepository('SampleAppBundle:Account')
            ->findAll();

        // The type of the view will automatically
        // be found based on the Accept header.
        $parameters = [
            'accounts' => $accounts
        ];

        return $this->renderResource('SampleAppBundle:Accounts:accounts', $parameters);
    }

}
```

You must describe what content types this resource supports. This means if a client sends an Accept header with a content type this resource does not accept, a `406 Unacceptable` response will be returned. Because this resource supports three content types, you must have three different views: accounts.json.twig, accounts.xml.twig, and accounts.html.twig.

### index.json.twig
```javascript
{% autoescape false %}
[
    {% for account in accounts %}
        {
            "id": {{ account.getId|json_encode }},
            "name": {{ account.getName|json_encode }}
        }
    {% endfor %}
]
{% endautoescape %}
```

### index.xml.twig
```xml
<?xml version="1.0" encoding="UTF-8"?>
<accounts>
    {% for account in accounts %}
        <account>
            <id>{{ account.getId }}</id>
            <name>{{ account.getName }}</name>
        </account>
    {% endfor %}
</accounts>
```

### index.html.twig
```html
<!doctype html>
<html>
<head>
    <title>Accounts Resource</title>
</head>
<body>
    <ul>
        {% for account in accounts %}
            <li>{{ account.getName }}</li>
        {% endfor %}
    </ul>
</body>
</html>
```

A slightly more advanced controller might find an entity and render it. You want to render it in the format requested. You would simply create the templates for those formats. Don't forget that resources should link to one another and each other, if necessary. Here is a sample template for an Account.

```javascript
{% autoescape false %}
{
    "id": {{ account.getId }},
    "created_at": {{ account.getCreatedAt|format_date|json_encode }},
    "updated_at": {{ account.getUpdatedAt|format_date|json_encode }},
    "name": {{ account.getName|json_encode }},
    "identifier": {{ account.getIdentifier|json_encode }},
    "email": {{ account.getEmail|json_encode }},
    "lang": {{ account.getLang|json_encode }},
    "_links": [
        {
            "rel": "self",
            "url": {{ url('sample_app_view_account', { 'id': account.getId })|json_encode }}
        },
        {
            "rel": "alt",
            "url": {{ url('sample_app_view_account_identifier', { 'identifier': account.getIdentifier })|json_encode }}
        }
    ]
}
{% endautoescape %}
```

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

You are responsible for rendering your errors. There is a default template located in `Resources/views/Restful/error.json.twig`. You should familiarize yourself with catching kernel exceptions in Symfony using an Event Listener to automatically return all errors as a RESTful response. The error response template looks like this:

```javascript
{% autoescape false %}
{
    "httpCode": {{ httpCode }},
    "message": {{ message|json_encode }}
}
{% endautoescape %}
```

The HTTP code will also be sent back as part of the response payload. For example, making this request to the resource above would result in a 406 error:

```bash
$ curl -v -H "Accept: invalid/type" http://example.com/

< HTTP/1.1 406 Not Acceptable
< Content-Length: 195
< Content-Type: application/json; charset=utf-8
< 
{
    "httpCode": 406,
    "message": "This resource can not respond with a format the client will find acceptable. This resource supports: [application\/json, application\/xml, text\/html]."
}
```

If the error code of an exception can not be determine, the default 500 error code is used. More `HttpException` classes will be added as needed.

## License
The MIT License (MIT)

Copyright (c) 2012-2014 Vic Cherubini, Bright March, LLC
