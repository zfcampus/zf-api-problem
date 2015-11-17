<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\ApiProblem\View;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\ViewEvent;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ApiProblem\View\ApiProblemRenderer;
use ZF\ApiProblem\View\ApiProblemStrategy;

/**
 * @subpackage UnitTest
 */
class ApiProblemStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->response = new Response;
        $this->event    = new ViewEvent;
        $this->event->setResponse($this->response);

        $this->renderer = new ApiProblemRenderer;
        $this->strategy = new ApiProblemStrategy($this->renderer);
    }

    public function invalidViewModels()
    {
        return [
            'null'    => [null],
            'generic' => [new ViewModel()],
            'json'    => [new JsonModel()],
        ];
    }

    /**
     * @dataProvider invalidViewModels
     */
    public function testSelectRendererReturnsNullIfModelIsNotAnApiProblemModel($model)
    {
        if (null !== $model) {
            $this->event->setModel($model);
        }
        $this->assertNull($this->strategy->selectRenderer($this->event));
    }

    public function testSelectRendererReturnsRendererIfModelIsAnApiProblemModel()
    {
        $model = new ApiProblemModel();
        $this->event->setModel($model);
        $this->assertSame($this->renderer, $this->strategy->selectRenderer($this->event));
    }

    public function testInjectResponseDoesNotSetContentTypeHeaderIfResultIsNotString()
    {
        $this->event->setRenderer($this->renderer);
        $this->event->setResult(['foo']);
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        $this->assertFalse($headers->has('Content-Type'));
    }

    public function testInjectResponseSetsContentTypeHeaderToApiProblemForApiProblemModel()
    {
        $problem = new ApiProblem(500, 'whatever', 'foo', 'bar');
        $model   = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);
        $headers = $this->response->getHeaders();
        $this->assertTrue($headers->has('Content-Type'));
        $header = $headers->get('Content-Type');
        $this->assertEquals(ApiProblem::CONTENT_TYPE, $header->getFieldValue());
    }

    public function invalidStatusCodes()
    {
        return [
            [0],
            [1],
            [99],
            [600],
            [10081],
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testUsesStatusCode500ForAnyStatusCodesAbove599OrBelow100($status)
    {
        $problem = new ApiProblem($status, 'whatever');
        $model   = new ApiProblemModel($problem);
        $this->event->setModel($model);
        $this->event->setRenderer($this->renderer);
        $this->event->setResult('{"foo":"bar"}');
        $this->strategy->injectResponse($this->event);

        $this->assertEquals(500, $this->response->getStatusCode());
    }
}
