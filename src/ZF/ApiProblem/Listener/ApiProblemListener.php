<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiProblem\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
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
    protected static $acceptFilter = array(
        'application/json',
        'application/*+json',
    );

    /**
     * Constructor
     *
     * Set the accept filter, if one is passed
     *
     * @param string|array $filter
     */
    public function __construct($filter = null)
    {
        if (is_string($filter) && !empty($filter)) {
            static::$acceptFilter = array($filter);
        }
        if (is_array($filter) && !empty($filter)) {
            static::$acceptFilter = $filter;
        }
    }

    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, __CLASS__ . '::onRender', 1000);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, __CLASS__ . '::onDispatchError', 100);
    }

    /**
     * Listen to the render event
     *
     * @param MvcEvent $e
     */
    public static function onRender(MvcEvent $e)
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
        if (!static::matchAcceptCriteria($accept)) {
            return;
        }

        // Next, do we have a view model in the result?
        // If not, nothing more to do.
        $model = $e->getResult();
        if (!$model instanceof ModelInterface) {
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
     * Handle render errors
     *
     * If the event representes an error, and has an exception composed, marshals an ApiProblemModel based on the exception, sets that as the event result 
     * and view model, and stops event propagation.
     * 
     * @param  MvcEvent $e 
     */
    public static function onDispatchError(MvcEvent $e)
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
     * @param  \Zend\Http\Header\Accept $accept 
     * @return bool
     */
    protected static function matchAcceptCriteria($accept)
    {
        foreach (static::$acceptFilter as $type) {
            $match = $accept->match($type);
            if ($match && $match->getTypeString() != '*/*') {
                return true;
            }
        }
        return false;
    }
}
