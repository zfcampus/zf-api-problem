<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\View;

use Zend\View\Model\ViewModel;
use ZF\ApiProblem\ApiProblem;

class ApiProblemModel extends ViewModel
{
    /**
     * @var string
     */
    protected $captureTo = 'errors';

    /**
     * @var ApiProblem
     */
    protected $problem;

    /**
     * @var bool
     */
    protected $terminate = true;

    /**
     * @param ApiProblem|null $problem
     */
    public function __construct(ApiProblem $problem = null)
    {
        if ($problem instanceof ApiProblem) {
            $this->setApiProblem($problem);
        }
    }

    /**
     * @param  ApiProblem $problem
     * @return ApiProblemModel
     */
    public function setApiProblem(ApiProblem $problem)
    {
        $this->problem = $problem;
        return $this;
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem()
    {
        return $this->problem;
    }
}
