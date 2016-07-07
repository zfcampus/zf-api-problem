<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use ZF\ApiProblem\View\ApiProblemRenderer;

class ApiProblemRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApiProblemRenderer
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $renderer = new ApiProblemRenderer();
        $renderer->setDisplayExceptions($displayExceptions);

        return $renderer;
    }
}
