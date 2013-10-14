<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    'service_manager' => array(
        'factories' => array(
            'ZF\ApiProblem\Listener\ApiProblemListener'  => 'ZF\ApiProblem\Factory\ApiProblemListenerFactory',
            'ZF\ApiProblem\Listener\RenderErrorListener' => 'ZF\ApiProblem\Factory\RenderErrorListenerFactory',
            'ZF\ApiProblem\View\ApiProblemRenderer'      => 'ZF\ApiProblem\Factory\ApiProblemRendererFactory',
            'ZF\ApiProblem\View\ApiProblemStrategy'      => 'ZF\ApiProblem\Factory\ApiProblemStrategyFactory',
        )
    ),

    'view_manager' => array(
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ),
);
