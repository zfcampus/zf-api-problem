<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Response as ConsoleResponse;
use Zend\Http\Request;
use Zend\Http\Response;
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

    public function testOnDispatchErrorSetsAnApiProblemModelResultBasedOnCurrentEventException()
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new DomainException('triggering exception', 400));
        $event->setRequest($request);
        $this->listener->onDispatchError($event);

        $this->assertTrue($event->propagationIsStopped());
        $result = $event->getResult();
        $this->assertInstanceOf('ZF\ApiProblem\View\ApiProblemModel', $result);
        $problem = $result->getApiProblem();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblem', $problem);
        $this->assertEquals(400, $problem->http_status);
        $this->assertSame($event->getParam('exception'), $problem->detail);
    }

    public function testOnRenderErrorCreatesAnApiProblemResponse()
    {
        $response = new Response();
        $request  = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setRequest($request);
        $event->setResponse($response);
        $this->listener->onRenderError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertSame($response, $event->getResponse());

        $this->assertEquals(406, $response->getStatusCode());
        $headers = $response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertEquals('application/api-problem+json', $headers->get('content-type')->getFieldValue());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('httpStatus', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('describedBy', $content);
        $this->assertArrayHasKey('detail', $content);

        $this->assertEquals(406, $content['httpStatus']);
        $this->assertEquals('Not Acceptable', $content['title']);
        $this->assertContains('www.w3.org', $content['describedBy']);
        $this->assertContains('accept', $content['detail']);
    }
}
