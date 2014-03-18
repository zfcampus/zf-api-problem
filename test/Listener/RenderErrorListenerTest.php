<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZF\ApiProblem\Listener\RenderErrorListener;

class RenderErrorListenerTest extends TestCase
{
    /**
     * @var RenderErrorListener
     */
    protected $listener;

    public function setUp()
    {
        $this->listener = new RenderErrorListener();
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
        $this->assertEquals('application/problem+json', $headers->get('content-type')->getFieldValue());
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
}
