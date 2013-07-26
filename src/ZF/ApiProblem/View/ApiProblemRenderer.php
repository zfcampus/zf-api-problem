<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiProblem\View;

use Zend\View\Renderer\JsonRenderer;
use ZF\ApiProblem\ApiProblem;

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
     * @return RestfulJsonRenderer
     */
    public function setDisplayExceptions($flag)
    {
        $this->displayExceptions = (bool) $flag;
        return $this;
    }

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
