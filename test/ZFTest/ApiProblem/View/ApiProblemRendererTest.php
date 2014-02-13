<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\View;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ApiProblem\View\ApiProblemRenderer;

/**
 * @subpackage UnitTest
 */
class ApiProblemRendererTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = new ApiProblemRenderer();
    }

    public function testRendersApiProblemCorrectly()
    {
        $apiProblem = new ApiProblem(401, 'login error', 'http://status.dev/errors.md', 'Unauthorized');
        $model      = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $test = $this->renderer->render($model);
        $expected = array(
            'status' => 401,
            'type'   => 'http://status.dev/errors.md',
            'title'  => 'Unauthorized',
            'detail' => 'login error',
        );
        $this->assertEquals($expected, json_decode($test, true));
    }

    public function testCanHintToApiProblemToRenderStackTrace()
    {
        $exception  = new \Exception('exception message', 500);
        $apiProblem = new ApiProblem(500, $exception);
        $model      = new ApiProblemModel();
        $model->setApiProblem($apiProblem);
        $this->renderer->setDisplayExceptions(true);
        $test = $this->renderer->render($model);
        $test = json_decode($test, true);
        $this->assertArrayHasKey('trace', $test);
        $this->assertInternalType('array', $test['trace']);
        $this->assertGreaterThanOrEqual(1, count($test['trace']));
    }
}
