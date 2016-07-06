<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

return [
    'service_manager' => [
        'aliases'   => [
            ApiProblemListener::class  => Listener\ApiProblemListener::class,
            RenderErrorListener::class => Listener\RenderErrorListener::class,
            ApiProblemRenderer::class  => View\ApiProblemRenderer::class,
            ApiProblemStrategy::class  => View\ApiProblemStrategy::class,
        ],
        'factories' => [
            Listener\ApiProblemListener::class             => Factory\ApiProblemListenerFactory::class,
            Listener\RenderErrorListener::class            => Factory\RenderErrorListenerFactory::class,
            Listener\SendApiProblemResponseListener::class => Factory\SendApiProblemResponseListenerFactory::class,
            View\ApiProblemRenderer::class                 => Factory\ApiProblemRendererFactory::class,
            View\ApiProblemStrategy::class                 => Factory\ApiProblemStrategyFactory::class,
        ],
    ],

    'view_manager' => [
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ],

    'zf-api-problem' => [
        // Accept types that should allow ApiProblem responses
        // 'accept_filters' => $stringOrArray,

        // Array of controller service names that should enable the ApiProblem render.error listener
        //'render_error_controllers' => [],
    ],
];
