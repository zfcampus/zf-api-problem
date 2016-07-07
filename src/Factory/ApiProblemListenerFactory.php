<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use ZF\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return ApiProblemListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $filters = null;
        $config = [];

        if ($container->has('config')) {
            $config = $container->get('config');
        }

        if (isset($config['zf-api-problem']['accept_filters'])) {
            $filters = $config['zf-api-problem']['accept_filters'];
        }

        return new ApiProblemListener($filters);
    }
}
