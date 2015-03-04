<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZF\ApiProblem\Exception\DomainException;
use ZF\ApiProblem\Listener\ApiProblemListener;

class ApiProblemListenerTest extends TestCase
{
    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var ApiProblemListener
     */
    protected $listener;

    public function setUp()
    {
        $this->event    = new MvcEvent();
        $this->event->setError('this is an error event');
        $this->listener = new ApiProblemListener();
    }

    public function testOnRenderReturnsEarlyWhenConsoleRequestDetected()
    {
        $this->event->setRequest(new ConsoleRequest());

        $this->assertNull($this->listener->onRender($this->event));
    }

    /**
     * Short description for the function
     *
     * @param $exceptionCode
     *
     * @return void
     *
     * @dataProvider providePossibleExceptionCodes
     */
    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventApiProblemExceptionInterface($exceptionCode)
    {
        $exception = new DomainException('triggering exception', $exceptionCode);

        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', $exception);
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals($exceptionCode, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }

    /**
     * Short description for the function
     *
     * @param $exceptionCode
     *
     * @return void
     *
     * @dataProvider providePossibleExceptionCodes
     */
    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventException($exceptionCode)
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new \Exception('triggering exception', $exceptionCode));
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $return);
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals(500, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }

    public function providePossibleExceptionCodes()
    {
        return array(
            array(PHP_INT_MAX ^ -1), // PHP_INT_MIN
            array(-8000),
            array(-500),
            array(-400),
            array(-1),
            array(0),
            array(1),
            array(200),
            array(500),
            array(8000),
            array(PHP_INT_MAX),
        );
    }
}
