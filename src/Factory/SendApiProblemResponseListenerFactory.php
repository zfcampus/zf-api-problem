<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Zend\Http\Response as HttpResponse;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\ApiProblem\Listener\SendApiProblemResponseListener;

class SendApiProblemResponseListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return SendApiProblemResponseListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config            = $serviceLocator->get('Config');
        $displayExceptions = isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
            && $config['view_manager']['display_exceptions'];

        $listener = new SendApiProblemResponseListener();
        $listener->setDisplayExceptions($displayExceptions);

        if ($serviceLocator->has('Response')) {
            $response = $serviceLocator->get('Response');
            if ($response instanceof HttpResponse) {
                $listener->setApplicationResponse($response);
            }
        }

        return $listener;
    }
}
