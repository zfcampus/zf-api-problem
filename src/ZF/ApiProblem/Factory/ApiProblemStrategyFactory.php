<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\View\ApiProblemStrategy;

class ApiProblemStrategyFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ApiProblemStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiProblemStrategy($serviceLocator->get('ZF\ApiProblem\ApiProblemRenderer'));
    }
}
