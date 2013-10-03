<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\View;

use Zend\View\Model\ViewModel;
use ZF\ApiProblem\ApiProblem;

class ApiProblemModel extends ViewModel
{
    protected $captureTo = 'errors';
    protected $problem;
    protected $terminate = true;

    public function __construct($problem = null, $options = null)
    {
        if ($problem instanceof ApiProblem) {
            $this->setApiProblem($problem);
        }
    }

    public function setApiProblem(ApiProblem $problem)
    {
        $this->problem = $problem;
        return $this;
    }

    public function getApiProblem()
    {
        return $this->problem;
    }
}
