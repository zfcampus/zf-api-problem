<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use ZF\ApiProblem\View\ApiProblemRenderer;
use ZF\ApiProblem\View\ApiProblemStrategy;

class ApiProblemStrategyFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApiProblemStrategy
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ApiProblemStrategy($container->get(ApiProblemRenderer::class));
    }
}
