<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

/**
 * ZF2 module
 */
class Module
{
    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array('namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ))
        );
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

    /**
     * Listener for bootstrap event
     *
     * Attaches a render event.
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onBootstrap($e)
    {
        $app            = $e->getTarget();
        $serviceManager = $app->getServiceManager();
        $eventManager   = $app->getEventManager();

        $eventManager->attach($serviceManager->get('ZF\ApiProblem\ApiProblemListener'));
        $eventManager->attach('render', array($this, 'onRender'), 100);

        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', $e::EVENT_DISPATCH, array($this, 'onDispatch'), 100);
    }

    public function onDispatch($e)
    {
        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $config   = $services->get('Config');
        if (!isset($config['zf_api_problem'])) {
            return;
        }
        if (!isset($config['zf_api_problem']['render_error_controllers'])) {
            return;
        }

        $controller  = $e->getRouteMatch()->getParam('controller');
        $controllers = $config['zf_api_problem']['render_error_controllers'];
        if (!in_array($controller, $controllers)) {
            // The current controller is not in our list of controllers to handle
            return;
        }

        // Attach the ApiProblem render.error listener
        $events = $app->getEventManager();
        $events->attach($services->get('ZF\ApiProblem\RenderErrorListener'));
    }

    /**
     * Listener for the render event
     *
     * Attaches a rendering/response strategy to the View.
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onRender($e)
    {
        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $view     = $services->get('View');
        $events   = $view->getEventManager();

        // register at high priority, to "beat" normal json strategy registered
        // via view manager, as well as HAL strategy.
        $events->attach($services->get('ZF\ApiProblem\ApiProblemStrategy'), 400);
    }
}
