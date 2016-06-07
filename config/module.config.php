<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

use ZF\ApiProblem\Factory\ApiProblemListenerFactory;
use ZF\ApiProblem\Factory\ApiProblemRendererFactory;
use ZF\ApiProblem\Factory\ApiProblemStrategyFactory;
use ZF\ApiProblem\Factory\RenderErrorListenerFactory;
use ZF\ApiProblem\Factory\SendApiProblemResponseListenerFactory;
use ZF\ApiProblem\Listener\ApiProblemListener;
use ZF\ApiProblem\Listener\RenderErrorListener;
use ZF\ApiProblem\Listener\SendApiProblemResponseListener;
use ZF\ApiProblem\View\ApiProblemRenderer;
use ZF\ApiProblem\View\ApiProblemStrategy;

return [
    'service_manager' => [
        'aliases'   => [
            'ZF\ApiProblem\ApiProblemListener'  => ApiProblemListener::class,
            'ZF\ApiProblem\RenderErrorListener' => RenderErrorListener::class,
            'ZF\ApiProblem\ApiProblemRenderer'  => ApiProblemRenderer::class,
            'ZF\ApiProblem\ApiProblemStrategy'  => ApiProblemStrategy::class,
        ],
        'factories' => [
            ApiProblemListener::class             => ApiProblemListenerFactory::class,
            RenderErrorListener::class            => RenderErrorListenerFactory::class,
            SendApiProblemResponseListener::class => SendApiProblemResponseListenerFactory::class,
            ApiProblemRenderer::class             => ApiProblemRendererFactory::class,
            ApiProblemStrategy::class             => ApiProblemStrategyFactory::class,
        ],
    ],

    'view_manager' => [
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => FALSE,
    ],

    'zf-api-problem' => [
        // Accept types that should allow ApiProblem responses
        // 'accept_filters' => $stringOrArray,

        // Array of controller service names that should enable the ApiProblem render.error listener
        //'render_error_controllers' => array(),
    ],
];
