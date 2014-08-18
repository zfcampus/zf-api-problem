ZF Api Problem
==============

[![Build Status](https://travis-ci.org/zfcampus/zf-api-problem.png)](https://travis-ci.org/zfcampus/zf-api-problem)

Introduction
------------

This module provides data structures and rendering for the API-Problem format.

- [Problem Details for HTTP APIs](http://tools.ietf.org/html/draft-nottingham-http-problem-06),
  used for reporting API problems.

Requirements
------------
  
Please see the [composer.json](composer.json) file.

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

#### Key: accept_filters

An array of `Accept` header media types that, when matched, will result in the
[ApiProblemListener](#zfapiproblemlistenerapiproblemlistener) handling an
`MvcEvent::EVENT_RENDER_ERROR` event.

#### Key: render_error_controllers

An array of controller service names that, if matched as the `controller` parameter in the MVC
`RouteMatch`, will cause the [ApiProblemListener](#zfapiproblemlistenerapiproblemlistener) to handle 
`MvcEvent::EVENT_RENDER_ERROR` events.

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
    ),
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

#### ZF\ApiProblem\Listener\ApiProblemListener

The `ApiProblemListener` attaches to three events in the MVC lifecycle:

- `MvcEvent::EVENT_DISPATCH` as a _shared_ listener on `Zend\Stdlib\DispatchableInterface` with a
  priority of `100`.
- `MvcEvent::EVENT_DISPATCH_ERROR` with a priority of `100`.
- `MvcEvent::EVENT_RENDER` with a priority of `1000`.

If the current `Accept` media type does not match the configured API-Problem media types (by
default, these are `application/json` and `application/*+json`), then this listener returns without
taking any action.

When this listener does take action, the purposes are threefold:

- Before dispatching, the `render_error_controllers` configuration value is consulted to determine
  if the `ZF\ApiProblem\Listener\RenderErrorListener` should be attached; see
  [RenderErrorListener](#rendererrorlistener) for more information.
- After dispatching, detects the type of response from the controller; if it is already an
  `ApiProblem` model, it continues without doing anything. If an exception was thrown during
  dispatch, it converts the response to an API-Problem response with some information from the
  exception.
- If a dispatch error occurred, and the `Accept` type is in the set defined for API-Problems, it
  attempts to cast the dispatch exception into an API-Problem response.

#### ZF\ApiProblem\Listener\RenderErrorListener

This listener is attached to `MvcEvent::EVENT_RENDER_ERROR` at priority `100`.  This listener is
conditionally attached by `ZF\ApiProblem\Listener\ApiProblemListener` for controllers that require
API Problem responses.  With a priority of `100`, this ensures that this listener runs before the
default ZF2 listener on this event. In cases when it does run, it will cast an exception into an
API-problem response.

#### `ZF\ApiProblem\Listener\SendApiProblemResponseListener`

This listener is attached to `SendResponseEvent::EVENT_SEND_RESPONSE` at priority `-500`.  The
primary purpose of this listener is, on detection of an API-Problem response, to send appropriate
headers and the problem details as the content body. If the `view_manager`'s `display_exceptions`
setting is enabled, the listener will determine if the API-Problem represents an application
exception, and, if so, inject the exception trace as part of the serialized response.

ZF2 Services
------------

### Event Services

- `ZF\ApiProblem\Listener\ApiProblemListener`
- `ZF\ApiProblem\Listener\RenderErrorListener`
- `ZF\ApiProblem\Listener\SendApiProblemResponseListener`

### View Services

#### ZF\ApiProblem\View\ApiProblemRenderer

This service extends the `JsonRenderer` service from the ZF2 MVC layer.  Its primary responsibility
is to decorate JSON rendering with the ability to optionally output stack traces.

#### ZF\ApiProblem\View\ApiProblemStrategy

This service is a view strategy that detects a `ZF\ApiProblem\View\ApiProblemModel`; when detected,
it selects the [ApiProblemRender](#zfapiproblemviewapiproblemrenderer), and injects the response
with a `Content-Type` header that contains the `application/problem+json` media type. This is
similar in nature to Zend Framework 2's `JsonStrategy`.

### Models

#### ZF\ApiProblem\ApiProblem

An instance of `ZF\ApiProblem\ApiProblem` serves the purpose of modeling the kind of problem that is
encountered.  An instance of `ApiProblem` is typically wrapped in an
[ApiProblemResponse](#zfapiproblemapiproblemresponse). Most information can be passed into the
constructor:

```php
class ApiProblem {
    public function __construct(
        $status,
        $detail,
        $type = null,
        $title = null,
        array $additional = array()
    ) {
        /* ... */
    }
}
```

For example:

```php
new ApiProblem(404, 'Entity not found');

// or

new ApiProblem(424, $exceptionInstance);
```

#### `ZF\ApiProblem\ApiProblemResponse`

An instance of `ZF\ApiProblem\ApiProblemResponse` can be returned from any controller service or ZF2
MVC event in order to short-circuit the MVC lifecycle and immediately return a response. When it
is, the response will be converted to the proper JSON structure for an API-Problem, and the
`Content-Type` header will be set to the `application/problem+json` media type.

For example:

```php
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class MyController extends AbstractActionController
{
    /* ... */
    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (! $entity) {
            return new ApiProblemResponse(ApiProblem(404, 'Entity not found'));
        }
        return $entity;
    }
}
```
