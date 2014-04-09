ZF Api Problem
==============

[![Build Status](https://travis-ci.org/zfcampus/zf-api-problem.png)](https://travis-ci.org/zfcampus/zf-api-problem)

Introduction
------------

This module provides data structures and rendering for the API-Problem format.

- [Problem API](http://tools.ietf.org/html/draft-nottingham-http-problem-05),
  used for reporting API problems


Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-api-problem:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-api-problem": "~1.0-dev"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'ZF\ApiProblem',
    ),
    /* ... */
);
```

Configuration
-------------

### User Configuration

The top-level configuration key for user configuration of this module is `zf-api-problem`.

#### Key: `accept_filters`

// Accept types that should allow ApiProblem responses

#### Key: `render_error_controllers`

// Array of controller service names that should enable the ApiProblem render.error listener


### System Configuration

The following configuration is provided in `config/module.config.php` to enable the module to
function:

```php
'service_manager' => array(
    'aliases'   => array(
        'ZF\ApiProblem\ApiProblemListener'  => 'ZF\ApiProblem\Listener\ApiProblemListener',
        'ZF\ApiProblem\RenderErrorListener' => 'ZF\ApiProblem\Listener\RenderErrorListener',
        'ZF\ApiProblem\ApiProblemRenderer'  => 'ZF\ApiProblem\View\ApiProblemRenderer',
        'ZF\ApiProblem\ApiProblemStrategy'  => 'ZF\ApiProblem\View\ApiProblemStrategy',
    ),
    'factories' => array(
        'ZF\ApiProblem\Listener\ApiProblemListener'             => 'ZF\ApiProblem\Factory\ApiProblemListenerFactory',
        'ZF\ApiProblem\Listener\RenderErrorListener'            => 'ZF\ApiProblem\Factory\RenderErrorListenerFactory',
        'ZF\ApiProblem\Listener\SendApiProblemResponseListener' => 'ZF\ApiProblem\Factory\SendApiProblemResponseListenerFactory',
        'ZF\ApiProblem\View\ApiProblemRenderer'                 => 'ZF\ApiProblem\Factory\ApiProblemRendererFactory',
        'ZF\ApiProblem\View\ApiProblemStrategy'                 => 'ZF\ApiProblem\Factory\ApiProblemStrategyFactory',
    )
),
'view_manager' => array(
    // Enable this in your application configuration in order to get full
    // exception stack traces in your API-Problem responses.
    'display_exceptions' => false,
),
```

ZF2 Events
----------

### Listeners

#### `ZF\ApiProblem\Listener\ApiProblemListener`

The `ApiProblemListener` attaches to three events in the MVC lifecycle:

- `MvcEvent::EVENT_RENDER` with a priority of 1000
- `MvcEvent::EVENT_DISPATCH_ERROR` with a priority of 100
- `MvcEvent::EVENT_DISPATCH` as a _shared_ event on `Zend\Stdlib\DispatchableInterface` with a
  priority of 100

If the current Accept media type does not match the configured api-problem media types,
then this listener passes on taking any action.

When this listener does take action, the purposes are threefold:

- Before dispatching, the `render_error_controllers` configuration value is consulted to determine
  if the `ZF\ApiProblem\Listener\RenderErrorListener` should be attached, see
  `RenderErrorListener` for more information.
- After dispatching, detect the type of response from the controller, if it is already an
  ApiProblem model, continue.  If not, but there was an exception throw during dispatch,
  convert the response to an api-problem with some information as to what the exception is.
- If a dispatch error has occurred, and is a valid Accept type, attempt to cast the dispatch
  exception into an API problem response.

#### `ZF\ApiProblem\Listener\RenderErrorListener`

This listener is attached to `MvcEvent::EVENT_RENDER_ERROR` at priority `100`.  This listener
is conditionally attached by `ZF\ApiProblem\Listener\ApiProblemListener` for controllers that
require API Problem responses.  With a priority of `100`, this ensures that this Render Error
time listener will run before the ZF2 one.  In cases when it does run, it will attempt to
take exceptions and inject an api-problem response in the HTTP response.

#### `ZF\ApiProblem\Listener\SendApiProblemResponseListener`

This listener is attached to the `SendResponseEvent::EVENT_SEND_RESPONSE` at priority `-500`.
The primary purpose of this listener is to, when an ApiProblem response is to be sent, to
send the headers and content body.  This differs from the way ZF2 typically sends responses
in that inside this listener it takes into account if the Response should include an
exception trace as part of the serialized response or not.

ZF2 Services
------------

### Event Services

- `ZF\ApiProblem\Listener\ApiProblemListener`
- `ZF\ApiProblem\Listener\RenderErrorListener`
- `ZF\ApiProblem\Listener\SendApiProblemResponseListener`

### View Services

#### `ZF\ApiProblem\View\ApiProblemRenderer`

This service extends the JsonRenderer service from the ZF2 MVC layer.  It's primary responsibility
is to decorate JSON rendering with the ability to optionally output stack traces.

#### `ZF\ApiProblem\View\ApiProblemStrategy`

This service is a view strategy that allows any ApiProblemModels details to be injected into the
MVC's HTTP response object.  This is similar in nature to the `JsonStrategy`.

### Models

#### `ZF\ApiProblem\ApiProblem`

An instance of `ZF\ApiProblem\ApiProblem` serves the purpose of modeling the kind of problem that
is encountered.  An instance of `ApiProblem` is typically wrapped in an `ApiProblemResponse`
(see `ApiProblemResponse` below).  Most information can be passed into the constructor:

```php
class ApiProblem {
  public function __construct($status, $detail, $type = null, $title = null, array $additional = array()) {}
}
```

For example:

```php
new ApiProblem(404, 'Entity not found');
// or
new ApiProblem(424, $exceptionInstance);
```

#### `ZF\ApiProblem\ApiProblemResponse`

An instance of `ZF\ApiProblem\ApiProblem` can be returned from any controller service or ZF2
MVC event.  When it is, the HTTP response will be converted to the proper JSON based HTTP response.

For example:

```php
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
class MyController extends Zend\Mvc\Controller\AbstractActionController
{
    /* ... */
    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (!$entity) {
            return new ApiProblemResponse(ApiProblem(404, 'Entity not found'));
        }
        return $entity;
    }
}
```