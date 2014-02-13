<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\View;

use Zend\View\Strategy\JsonStrategy;
use Zend\View\ViewEvent;
use ZF\ApiProblem\ApiProblem;

/**
 * Extension of the JSON strategy to handle the ApiProblemModel and provide
 * a Content-Type header appropriate to the response it describes.
 *
 * This will give the following content types:
 *
 * - application/problem+json for a result that contains a Problem
 *   API-formatted response
 */
class ApiProblemStrategy extends JsonStrategy
{
    /**
     * @param ApiProblemRenderer $renderer
     */
    public function __construct(ApiProblemRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Detect if we should use the ApiProblemRenderer based on model type.
     *
     * @param  ViewEvent $e
     * @return null|ApiProblemRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (!$model instanceof ApiProblemModel) {
            // unrecognized model; do nothing
            return;
        }

        // ApiProblemModel found
        return $this->renderer;
    }

    /**
     * Inject the response
     *
     * Injects the response with the rendered content, and sets the content
     * type based on the detection that occurred during renderer selection.
     *
     * @param  ViewEvent $e
     */
    public function injectResponse(ViewEvent $e)
    {
        $result = $e->getResult();
        if (!is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        $model = $e->getModel();
        if (!$model instanceof ApiProblemModel) {
            // Model is not an ApiProblemModel; we cannot handle it here
            return;
        }

        $problem     = $model->getApiProblem();
        $statusCode  = $this->getStatusCodeFromApiProblem($problem);
        $contentType = 'application/problem+json';

        // Populate response
        $response = $e->getResponse();
        $response->setStatusCode($statusCode);
        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', $contentType);
    }

    /**
     * Retrieve the HTTP status from an ApiProblem object
     *
     * Ensures that the status falls within the acceptable range (100 - 599).
     *
     * @param  ApiProblem $problem
     * @return int
     */
    protected function getStatusCodeFromApiProblem(ApiProblem $problem)
    {
        $status = $problem->status;

        if ($status < 100 || $status >= 600) {
            return 500;
        }

        return $status;
    }
}
