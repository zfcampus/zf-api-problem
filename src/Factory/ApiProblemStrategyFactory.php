<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\ApiProblem\View\ApiProblemRenderer;
use ZF\ApiProblem\View\ApiProblemStrategy;

/**
 * Class ApiProblemStrategyFactory
 *
 * @package ZF\ApiProblem\Factory
 */
class ApiProblemStrategyFactory implements FactoryInterface
{
    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|NULL                            $options
     *
     * @return \ZF\ApiProblem\View\ApiProblemStrategy
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        return new ApiProblemStrategy($container->get(ApiProblemRenderer::class));
    }

}
