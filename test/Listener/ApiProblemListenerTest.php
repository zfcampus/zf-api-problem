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
     * @group zf-hal-101
     */
    public function testOnDispatchAttachesRenderErrorListenerIfMatchedControllerIsInRenderErrorControllersList()
    {
        $config = array(
            'zf-api-problem' => array(
                'render_error_controllers' => array(
                    'FooController',
                ),
            ),
        );

        $renderErrorListener = $this->getMockBuilder('ZF\ApiProblem\RenderErrorListener')->getMock();

        $services = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $services
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Config'))
            ->willReturn($config);
        $services
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('ZF\ApiProblem\RenderErrorListener'))
            ->willReturn($renderErrorListener);

        $events = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $events
            ->expects($this->once())
            ->method('attach')
            ->with($this->equalTo($renderErrorListener));

        $app = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app
            ->expects($this->once())
            ->method('getServiceManager')
            ->willReturn($services);
        $app
            ->expects($this->once())
            ->method('getEventManager')
            ->willReturn($events);

        $routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')
            ->disableOriginalConstructor()
            ->getMock();
        $routeMatch
            ->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'))
            ->willReturn('FooController');

        $this->event->setApplication($app);
        $this->event->setRouteMatch($routeMatch);

        $this->listener->onDispatch($this->event);
    }

    public function apigilityServiceControllers()
    {
        return array(
            'FooController' => array('FooController'),
            'BarController' => array('BarController'),
            'BazController' => array('BazController'),
        );
    }

    public function getApigilityServiceControllerConfig()
    {
        return array(
            'zf-api-problem' => array(
                'render_error_controllers' => array(
                    'FooController',
                ),
            ),
            'zf-rest' => array(
                'BarController' => array(),
            ),
            'zf-rpc' => array(
                'BazController' => array(),
            ),
        );
    }

    /**
     * @group zf-hal-101
     * @dataProvider apigilityServiceControllers
     */
    public function testOnDispatchAttachesRenderErrorListenerIfMatchedControllerIsInKnownApigilityConfigs($controller)
    {
        $config = $this->getApigilityServiceControllerConfig();

        $renderErrorListener = $this->getMockBuilder('ZF\ApiProblem\RenderErrorListener')->getMock();

        $services = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $services
            ->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('Config'))
            ->willReturn($config);
        $services
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('ZF\ApiProblem\RenderErrorListener'))
            ->willReturn($renderErrorListener);

        $events = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $events
            ->expects($this->once())
            ->method('attach')
            ->with($this->equalTo($renderErrorListener));

        $app = $this->getMockBuilder('Zend\Mvc\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app
            ->expects($this->once())
            ->method('getServiceManager')
            ->willReturn($services);
        $app
            ->expects($this->once())
            ->method('getEventManager')
            ->willReturn($events);

        $routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')
            ->disableOriginalConstructor()
            ->getMock();
        $routeMatch
            ->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'))
            ->willReturn($controller);

        $this->event->setApplication($app);
        $this->event->setRouteMatch($routeMatch);

        $this->listener->onDispatch($this->event);
    }
}
