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
 * RenderErrorListener
 *
 * Provides a listener on the render.error event, at high priority.
 */
class RenderErrorListener extends AbstractListenerAggregate
{
    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, __CLASS__ . '::onRenderError', 100);
    }

    /**
     * Handle rendering errors
     *
     * Rendering errors are usually due to trying to render a template in 
     * the PhpRenderer, when we have no templates.
     *
     * As such, report as an unacceptable response.
     *
     * @param  MvcEvent $e 
     */
    public static function onRenderError(MvcEvent $e)
    {
        $response = $e->getResponse();
        $response->setStatusCode(406);
        $response->getHeaders()->addHeaderLine('content-type', 'application/api-problem+json');
        $response->setContent(json_encode(array(
            'httpStatus'  => 406,
            'title'       => 'Not Acceptable',
            'describedBy' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
            'detail'      => 'Your request could not be resolved to an acceptable representation.'
        )));

        $e->stopPropagation();
    }
}
