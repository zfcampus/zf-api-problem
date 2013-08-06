<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

return array(
    'zf-api-problem' => array(
    ),

    'service_manager' => array(
        'invokables' => array(
            'ZF\ApiProblem\RenderErrorListener' => 'ZF\ApiProblem\Listener\RenderErrorListener',
        ),
    ),

    'view_manager' => array(
        // Enable this in your application configuration in order to get full
        // exception stack traces in your API-Problem responses.
        'display_exceptions' => false,
    ),
);
