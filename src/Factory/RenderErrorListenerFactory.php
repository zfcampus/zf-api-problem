<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Factory;

use Interop\Container\ContainerInterface;
use ZF\ApiProblem\Listener\RenderErrorListener;

class RenderErrorListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return RenderErrorListener
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $displayExceptions = false;

        if (isset($config['view_manager'])
            && isset($config['view_manager']['display_exceptions'])
        ) {
            $displayExceptions = (bool) $config['view_manager']['display_exceptions'];
        }

        $listener = new RenderErrorListener();
        $listener->setDisplayExceptions($displayExceptions);

        return $listener;
    }
}
