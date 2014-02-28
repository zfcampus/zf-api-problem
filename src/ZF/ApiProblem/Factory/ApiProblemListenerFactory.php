<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return ApiProblemListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $filters = null;
        $config  = array();

        if ($serviceLocator->has('Config')) {
            $config = $serviceLocator->get('Config');
        }

        if (isset($config['zf-api-problem']['accept_filters'])) {
            $filters = $config['zf-api-problem']['accept_filters'];
        }

        return new ApiProblemListener($filters);
    }
}
