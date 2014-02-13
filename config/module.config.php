<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
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

    'zf-api-problem' => array(
        // Accept types that should allow ApiProblem responses
        // 'accept_filters' => $stringOrArray,

        // Array of controller service names that should enable the ApiProblem render.error listener
        //'render_error_controllers' => array(),
    )
);
