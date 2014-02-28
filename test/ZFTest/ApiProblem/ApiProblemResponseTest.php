<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class ApiProblemResponseTest extends TestCase
{
    public function testApiProblemResponseIsAnHttpResponse()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsStatusCodeAndReasonPhrase()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertInternalType('string', $response->getReasonPhrase());
        $this->assertNotEmpty($response->getReasonPhrase());
        $this->assertEquals('bad request', strtolower($response->getReasonPhrase()));
    }

    public function testApiProblemResponseBodyIsSerializedApiProblem()
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        $this->assertEquals($apiProblem->toArray(), json_decode($response->getContent(), true));
    }

    /**
     * @depends testApiProblemResponseIsAnHttpResponse
     */
    public function testApiProblemResponseSetsContentTypeHeader()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $headers  = $response->getHeaders();
        $this->assertTrue($headers->has('content-type'));
        $header = $headers->get('content-type');
        $this->assertInstanceOf('Zend\Http\Header\ContentType', $header);
        $this->assertEquals('application/problem+json', $header->getFieldValue());
    }

    public function testComposeApiProblemIsAccessible()
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        $this->assertSame($apiProblem, $response->getApiProblem());
    }

    /**
     * @group 14
     */
    public function testOverridesReasonPhraseIfStatusCodeIsUnknown()
    {
        $response = new ApiProblemResponse(new ApiProblem(7, 'Random error'));
        $this->assertContains('Unknown', $response->getReasonPhrase());
    }
}
