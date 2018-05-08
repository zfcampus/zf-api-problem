<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\Listener\RenderErrorListener;

class RenderErrorListenerTest extends TestCase
{
    /**
     * @var RenderErrorListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new RenderErrorListener();
    }

    public function testOnRenderErrorCreatesAnApiProblemResponse()
    {
        $response = new Response();
        $request = new Request();
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
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('describedBy', $content);
        $this->assertArrayHasKey('detail', $content);

        $this->assertEquals(406, $content['status']);
        $this->assertEquals('Not Acceptable', $content['title']);
        $this->assertContains('www.w3.org', $content['describedBy']);
        $this->assertContains('accept', $content['detail']);
    }

    public function testOnRenderErrorCreatesAnApiProblemResponseFromException()
    {
        $response = new Response();
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new RuntimeException('exception', 400));
        $event->setRequest($request);
        $event->setResponse($response);

        $this->listener->setDisplayExceptions(true);
        $this->listener->onRenderError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertSame($response, $event->getResponse());

        $this->assertEquals(400, $response->getStatusCode());
        $headers = $response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('describedBy', $content);
        $this->assertArrayHasKey('detail', $content);
        $this->assertArrayHasKey('details', $content);

        $this->assertEquals(400, $content['status']);
        $this->assertEquals('Unexpected error', $content['title']);
        $this->assertContains('www.w3.org', $content['describedBy']);
        $this->assertEquals('exception', $content['detail']);

        $this->assertInternalType('array', $content['details']);
        $details = $content['details'];
        $this->assertArrayHasKey('code', $details);
        $this->assertArrayHasKey('message', $details);
        $this->assertArrayHasKey('trace', $details);
        $this->assertEquals(400, $details['code']);
        $this->assertEquals('exception', $details['message']);
    }

    /**
     * @requires PHP 7.0
     */
    public function testOnRenderErrorCreatesAnApiProblemResponseFromThrowable()
    {
        $response = new Response();
        $request = new Request();
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $event = new MvcEvent();
        $event->setError(Application::ERROR_EXCEPTION);
        $event->setParam('exception', new TypeError('throwable', 400));
        $event->setRequest($request);
        $event->setResponse($response);

        $this->listener->setDisplayExceptions(true);
        $this->listener->onRenderError($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertSame($response, $event->getResponse());

        $this->assertEquals(400, $response->getStatusCode());
        $headers = $response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $headers->get('content-type')->getFieldValue());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('title', $content);
        $this->assertArrayHasKey('describedBy', $content);
        $this->assertArrayHasKey('detail', $content);
        $this->assertArrayHasKey('details', $content);

        $this->assertEquals(400, $content['status']);
        $this->assertEquals('Unexpected error', $content['title']);
        $this->assertContains('www.w3.org', $content['describedBy']);
        $this->assertEquals('throwable', $content['detail']);

        $this->assertInternalType('array', $content['details']);
        $details = $content['details'];
        $this->assertArrayHasKey('code', $details);
        $this->assertArrayHasKey('message', $details);
        $this->assertArrayHasKey('trace', $details);
        $this->assertEquals(400, $details['code']);
        $this->assertEquals('throwable', $details['message']);
    }
}
