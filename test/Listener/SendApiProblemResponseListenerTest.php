<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\ResponseSender\SendResponseEvent;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ApiProblem\Exception\DomainException;
use ZF\ApiProblem\Listener\SendApiProblemResponseListener;

class SendApiProblemResponseListenerTest extends TestCase
{
    public function setUp()
    {
        $this->exception  = new DomainException('Random error', 400);
        $this->apiProblem = new ApiProblem(400, $this->exception);
        $this->response   = new ApiProblemResponse($this->apiProblem);
        $this->event      = new SendResponseEvent();
        $this->event->setResponse($this->response);
        $this->listener   = new SendApiProblemResponseListener();
    }

    public function testListenerImplementsResponseSenderInterface()
    {
        $this->assertInstanceOf('Zend\Mvc\ResponseSender\ResponseSenderInterface', $this->listener);
    }

    public function testDisplayExceptionsFlagIsFalseByDefault()
    {
        $this->assertFalse($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testDisplayExceptionsFlagIsMutable()
    {
        $this->listener->setDisplayExceptions(true);
        $this->assertTrue($this->listener->displayExceptions());
    }

    /**
     * @depends testDisplayExceptionsFlagIsFalseByDefault
     */
    public function testSendContentDoesNotRenderExceptionsByDefault()
    {
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertInternalType('string', $contents);
        $data = json_decode($contents, true);
        $this->assertNotContains("\n", $data['detail']);
        $this->assertNotContains($this->exception->getTraceAsString(), $data['detail']);
    }

    public function testEnablingDisplayExceptionFlagRendersExceptionStackTrace()
    {
        $this->listener->setDisplayExceptions(true);
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertInternalType('string', $contents);
        $data = json_decode($contents, true);
        $this->assertArrayHasKey('trace', $data);
        $this->assertInternalType('array', $data['trace']);
        $this->assertGreaterThanOrEqual(1, count($data['trace']));
    }

    public function testSendContentDoesNothingIfEventDoesNotContainApiProblemResponse()
    {
        $this->event->setResponse(new HttpResponse);
        ob_start();
        $this->listener->sendContent($this->event);
        $contents = ob_get_clean();
        $this->assertInternalType('string', $contents);
        $this->assertEmpty($contents);
    }

    public function testSendHeadersMergesApplicationAndProblemHttpHeaders()
    {
        $appResponse = new HttpResponse();
        $appResponse->getHeaders()->addHeaderLine('Access-Control-Allow-Origin', '*');

        $listener = new SendApiProblemResponseListener();
        $listener->setApplicationResponse($appResponse);

        ob_start();
        $listener->sendHeaders($this->event);
        ob_get_clean();

        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Access-Control-Allow-Origin'));
    }
}
