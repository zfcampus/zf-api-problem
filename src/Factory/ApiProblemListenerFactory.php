<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerFactory implements FactoryInterface
{

    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|NULL                            $options
     *
     * @return \ZF\ApiProblem\Listener\ApiProblemListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        $filters = null;
        $config  = [];

        if ($container->has('Config')) {
            $config = $container->get('Config');
        }

        if (isset($config['zf-api-problem']['accept_filters'])) {
            $filters = $config['zf-api-problem']['accept_filters'];
        }

        return new ApiProblemListener($filters);
    }


}
