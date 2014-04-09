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

#### `ZF\ApiProblem\ApiProblemListener` (a.k.a. `ZF\ApiProblem\Listener\ApiProblemListener`)

#### `ZF\ApiProblem\RenderErrorListener` (a.k.a. `ZF\ApiProblem\Listener\RenderErrorListener`)

#### `ZF\ApiProblem\Listener\SendApiProblemResponseListener`


ZF2 Services
------------

### Event Services

- `ZF\ApiProblem\Listener\ApiProblemListener`
- `ZF\ApiProblem\Listener\RenderErrorListener`
- `ZF\ApiProblem\Listener\SendApiProblemResponseListener`

### View Services

#### `ZF\ApiProblem\ApiProblemRenderer` (a.k.a. `ZF\ApiProblem\View\ApiProblemRenderer`)

#### `ZF\ApiProblem\ApiProblemStrategy` (a.k.a. `ZF\ApiProblem\View\ApiProblemStrategy`)

