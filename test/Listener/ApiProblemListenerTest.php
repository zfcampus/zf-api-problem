<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Server\Response\Http;
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
     * @param $httpStatusCode
     *
     * @return void
     *
     * @dataProvider provideHttpStatusCodes
     */
    public function testOnDispatchErrorReturnsAnApiProblemResponseBasedOnCurrentEventApiProblemExceptionInterface($httpStatusCode)
    {
        $exception = new DomainException('triggering exception', 404);
        $exception->setHttpStatusCode($httpStatusCode);

        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', $exception);
        $event->setRequest($request);
        $return = $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $return);
        /** @var Http $response */
        $response = $event->getResponse();
        $this->assertSame($return, $response);
        $problem = $response->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals($httpStatusCode, $problem->status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }
    
    public function provideHttpStatusCodes()
    {
        return array(
            array(Response::STATUS_CODE_100),
            array(Response::STATUS_CODE_101),
            array(Response::STATUS_CODE_102),
            array(Response::STATUS_CODE_200),
            array(Response::STATUS_CODE_201),
            array(Response::STATUS_CODE_202),
            array(Response::STATUS_CODE_203),
            array(Response::STATUS_CODE_204),
            array(Response::STATUS_CODE_205),
            array(Response::STATUS_CODE_206),
            array(Response::STATUS_CODE_207),
            array(Response::STATUS_CODE_208),
            array(Response::STATUS_CODE_300),
            array(Response::STATUS_CODE_301),
            array(Response::STATUS_CODE_302),
            array(Response::STATUS_CODE_303),
            array(Response::STATUS_CODE_304),
            array(Response::STATUS_CODE_305),
            array(Response::STATUS_CODE_306),
            array(Response::STATUS_CODE_307),
            array(Response::STATUS_CODE_400),
            array(Response::STATUS_CODE_401),
            array(Response::STATUS_CODE_402),
            array(Response::STATUS_CODE_403),
            array(Response::STATUS_CODE_404),
            array(Response::STATUS_CODE_405),
            array(Response::STATUS_CODE_406),
            array(Response::STATUS_CODE_407),
            array(Response::STATUS_CODE_408),
            array(Response::STATUS_CODE_409),
            array(Response::STATUS_CODE_410),
            array(Response::STATUS_CODE_411),
            array(Response::STATUS_CODE_412),
            array(Response::STATUS_CODE_413),
            array(Response::STATUS_CODE_414),
            array(Response::STATUS_CODE_415),
            array(Response::STATUS_CODE_416),
            array(Response::STATUS_CODE_417),
            array(Response::STATUS_CODE_418),
            array(Response::STATUS_CODE_422),
            array(Response::STATUS_CODE_423),
            array(Response::STATUS_CODE_424),
            array(Response::STATUS_CODE_425),
            array(Response::STATUS_CODE_426),
            array(Response::STATUS_CODE_428),
            array(Response::STATUS_CODE_429),
            array(Response::STATUS_CODE_431),
            array(Response::STATUS_CODE_500),
            array(Response::STATUS_CODE_501),
            array(Response::STATUS_CODE_501),
            array(Response::STATUS_CODE_502),
            array(Response::STATUS_CODE_503),
            array(Response::STATUS_CODE_504),
            array(Response::STATUS_CODE_505),
            array(Response::STATUS_CODE_506),
            array(Response::STATUS_CODE_507),
            array(Response::STATUS_CODE_508),
            array(Response::STATUS_CODE_511),
        );
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
