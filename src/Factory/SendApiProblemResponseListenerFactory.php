<?php

/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZF\ApiProblem\Listener\SendApiProblemResponseListener;

class SendApiProblemResponseListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $listener = new SendApiProblemResponseListener();
        $listener->setDisplayExceptions($displayExceptions);

        if ($container->has('Response')) {
            $response = $container->get('Response');
            if ($response instanceof HttpResponse) {
                $listener->setApplicationResponse($response);
            }
        }

        return $listener;
    }
}
