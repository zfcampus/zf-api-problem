<?php

/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface;
use ZF\ApiProblem\Exception\DomainException;
use ZF\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = new MvcEvent();
        $this->event->setError('this is an error event');
        $this->listener = new ApiProblemListener();
    }

    public function testOnRenderReturnsEarlyWhenNonHttpRequestDetected()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $this->event->setRequest($request);

        $this->assertNull($this->listener->onRender($this->event));
    }

    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventException()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new DomainException('triggering exception', 400));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals(400, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }

    /**
     * @requires PHP 7.0
     */
    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventThrowable()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new \TypeError('triggering throwable', 400));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals(400, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }
}
