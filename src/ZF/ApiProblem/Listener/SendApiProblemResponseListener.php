<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Listener;

use Zend\Mvc\ResponseSender\HttpResponseSender;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use ZF\ApiProblem\ApiProblemResponse;

/**
 * Send ApiProblem responses
 */
class SendApiProblemResponseListener extends HttpResponseSender
{
    /**
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * Set the flag determining whether exception stack traces are included
     *
     * @param  bool $flag
     * @return self
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool) $flag;
        return $this;
    }

    /**
     * Are exception stack traces included in the response?
     *
     * @return bool
     */
    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    /**
     * Send the response content
     *
     * Sets the composed ApiProblem's flag for including the stack trace in the
     * detail based on the display exceptions flag, and then sends content.
     *
     * @param SendResponseEvent $e
     * @return self
     */
    public function sendContent(SendResponseEvent $e)
    {
        $response = $e->getResponse();
        if (!$response instanceof ApiProblemResponse) {
            return $this;
        }
        $response->getApiProblem()->setDetailIncludesStackTrace($this->displayExceptions());
        return parent::sendContent($e);
    }

    /**
     * Send ApiProblem response
     *
     * @param  SendResponseEvent $event
     * @return self
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (!$response instanceof ApiProblemResponse) {
            return $this;
        }

        $this->sendHeaders($event)
             ->sendContent($event);
        $event->stopPropagation(true);
        return $this;
    }
}
