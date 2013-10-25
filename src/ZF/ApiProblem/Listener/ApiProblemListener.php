<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\Http\Header\Accept as AcceptHeader;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ModelInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\Exception\ProblemExceptionInterface;
use ZF\ApiProblem\View\ApiProblemModel;

/**
 * ApiProblemListener
 *
 * Provides a listener on the render event, at high priority.
 *
 * If the MvcEvent represents an error, then its view model and result are
 * replaced with a RestfulJsonModel containing an API-Problem payload.
 */
class ApiProblemListener extends AbstractListenerAggregate
{
    /**
     * Default types to match in Accept header
     *
     * @var array
     */
    protected $acceptFilters = array(
        'application/json',
        'application/*+json',
    );

    /**
     * Constructor
     *
     * Set the accept filter, if one is passed
     *
     * @param string|array $filters
     */
    public function __construct($filters = null)
    {
        if (!empty($filters)) {
            if (is_string($filters)) {
                $this->acceptFilters = array($filters);
            }

            if (is_array($filters)) {
                $this->acceptFilters = $filters;
            }
        }
    }

    /**
     * @param    EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRender'), 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 100);

        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'onDispatch'),
            100
        );
    }

    /**
     * Listen to the render event
     *
     * @param MvcEvent $e
     */
    public function onRender(MvcEvent $e)
    {
        // only worried about error pages
        if (!$e->isError()) {
            return;
        }

        // and then, only if we have an Accept header...
        $request = $e->getRequest();
        if (!$request instanceof HttpRequest) {
            return;
        }

        $headers = $request->getHeaders();
        if (!$headers->has('Accept')) {
            return;
        }

        // ... that matches certain criteria
        $accept = $headers->get('Accept');
        if (!$this->matchAcceptCriteria($accept)) {
            return;
        }

        // Next, do we have a view model in the result?
        // If not, nothing more to do.
        $model = $e->getResult();
        if (!$model instanceof ModelInterface || $model instanceof ApiProblemModel) {
            return;
        }

        // Marshal the information we need for the API-Problem response
        $httpStatus = $e->getResponse()->getStatusCode();
        $exception  = $model->getVariable('exception');

        if ($exception instanceof \Exception) {
            $apiProblem = new ApiProblem($httpStatus, $exception);
        } else {
            $apiProblem = new ApiProblem($httpStatus, $model->getVariable('message'));
        }

        // Create a new model with the API-Problem payload, and reset
        // the result and view model in the event using it.
        $model = new ApiProblemModel($apiProblem);
        $e->setResult($model);
        $e->setViewModel($model);
    }

    /**
     * Handle dispatch
     *
     * It checks if the controller is in our list
     *
     * @param MvcEvent $e
     */
    public function onDispatch(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $services = $app->getServiceManager();
        $config   = $services->get('Config');
        if (!isset($config['zf-api-problem'])) {
            return;
        }
        if (!isset($config['zf-api-problem']['render_error_controllers'])) {
            return;
        }

        $controller  = $e->getRouteMatch()->getParam('controller');
        $controllers = $config['zf-api-problem']['render_error_controllers'];
        if (!in_array($controller, $controllers)) {
            // The current controller is not in our list of controllers to handle
            return;
        }

        // Attach the ApiProblem render.error listener
        $events = $app->getEventManager();
        $events->attach($services->get('ZF\ApiProblem\RenderErrorListener'));
    }

    /**
     * Handle render errors
     *
     * If the event representes an error, and has an exception composed, marshals an ApiProblemModel
     * based on the exception, sets that as the event result and view model, and stops event propagation.
     *
     * @param    MvcEvent $e
     */
    public function onDispatchError(MvcEvent $e)
    {
        // only worried about error pages
        if (!$e->isError()) {
            return;
        }

        // Marshall an ApiProblem and view model based on the exception
        $exception = $e->getParam('exception');
        if ($exception instanceof ProblemExceptionInterface) {
            $problem = new ApiProblem($exception->getCode(), $exception);
        } elseif ($exception instanceof \Exception) {
            $status = $exception->getCode();
            if (0 === $status) {
                $status = 500;
            }
            $detail  = $exception->getMessage();
            $problem = new ApiProblem($status, $detail);
        } else {
            // If it's not an exception, do not know what to do.
            return;
        }

        $model = new ApiProblemModel($problem);
        $e->setResult($model);
        $e->setViewModel($model);
        $e->stopPropagation();
    }

    /**
     * Attempt to match the accept criteria
     *
     * If it matches, but on "*\/*", return false.
     *
     * Otherwise, return based on whether or not one or more criteria match.
     * 
     * @param  AcceptHeader $accept
     * @return bool
     */
    protected function matchAcceptCriteria(AcceptHeader $accept)
    {
        foreach ($this->acceptFilters as $type) {
            $match = $accept->match($type);
            if ($match && $match->getTypeString() != '*/*') {
                return true;
            }
        }

        return false;
    }
}
