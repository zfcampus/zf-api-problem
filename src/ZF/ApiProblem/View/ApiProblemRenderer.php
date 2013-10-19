<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\View;

use Zend\View\Renderer\JsonRenderer;

class ApiProblemRenderer extends JsonRenderer
{
    /**
     * Whether or not to render exception stack traces in API-Problem payloads
     *
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * Set display_exceptions flag
     *
     * @param  bool $flag
     * @return void
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool) $flag;
    }

    /**
     * @param  string|\Zend\View\Model\ModelInterface $nameOrModel
     * @param  array|null $values
     * @return string
     */
    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof ApiProblemModel) {
            return '';
        }

        $apiProblem = $nameOrModel->getApiProblem();

        if ($this->displayExceptions) {
            $apiProblem->setDetailIncludesStackTrace(true);
        }

        return parent::render($apiProblem->toArray());
    }
}
