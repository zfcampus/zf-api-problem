<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

use Zend\Http\Response;

/**
 * Represents an ApiProblem response payload
 */
class ApiProblemResponse extends Response
{
    /**
     * @var ApiProblem
     */
    protected $apiProblem;

    /**
     * Flags to use with json_encode
     *
     * @var int
     */
    protected $jsonFlags = 0;

    /**
     * @param ApiProblem $apiProblem
     */
    public function __construct(ApiProblem $apiProblem)
    {
        $this->apiProblem = $apiProblem;
        $this->setCustomStatusCode($apiProblem->status);
        $this->setReasonPhrase($apiProblem->title);

        if (defined('JSON_UNESCAPED_SLASHES')) {
            $this->jsonFlags = constant('JSON_UNESCAPED_SLASHES');
        }
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem()
    {
        return $this->apiProblem;
    }

    /**
     * Retrieve the content
     *
     * Serializes the composed ApiProblem instance to JSON.
     *
     * @return string
     */
    public function getContent()
    {
        return json_encode($this->apiProblem->toArray(), $this->jsonFlags);
    }

    /**
     * Retrieve headers
     *
     * Proxies to parent class, but then checks if we have an content-type
     * header; if not, sets it, with a value of "application/problem+json".
     *
     * @return \Zend\Http\Headers
     */
    public function getHeaders()
    {
        $headers = parent::getHeaders();
        if (!$headers->has('content-type')) {
            $headers->addHeaderLine('content-type', 'application/problem+json');
        }
        return $headers;
    }

    /**
     * Override reason phrase handling
     *
     * If no corresponding reason phrase is available for the current status
     * code, return "Unknown Error".
     *
     * @return string
     */
    public function getReasonPhrase()
    {
        if (! empty($this->reasonPhrase)) {
            return $this->reasonPhrase;
        }

        if (isset($this->recommendedReasonPhrases[$this->statusCode])) {
            return $this->recommendedReasonPhrases[$this->statusCode];
        }

        return 'Unknown Error';
    }
}
