<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
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

    public function testApiProblemResponseBodyIsSerializedApiProblem()
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        $this->assertEquals($apiProblem->toArray(), json_decode($response->getContent(), true));
    }

    public function testApiProblemResponseSetsContentTypeHeader()
    {
        $response = new ApiProblemResponse(new ApiProblem(400, 'Random error'));
        $headers  = $response->getHeaders();
        $this->assertTrue($headers->has('accept'));
        $header = $headers->get('accept');
        $this->assertInstanceOf('Zend\Http\Header\Accept', $header);
        $this->assertEquals('application/api-problem+json', $header->getFieldValue());
    }

    public function testComposeApiProblemIsAccessible()
    {
        $apiProblem = new ApiProblem(400, 'Random error');
        $response   = new ApiProblemResponse($apiProblem);
        $this->assertSame($apiProblem, $response->getApiProblem());
    }
}
