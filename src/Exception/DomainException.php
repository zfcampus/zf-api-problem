<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Exception;

class DomainException extends \DomainException implements
    ExceptionInterface,
    ProblemExceptionInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $details = array();

    /**
     * @var string
     */
    protected $title;

    /**
     * @var integer
     */
    protected $httpStatusCode = 500;

    /**
     * Get httpStatusCode
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Set httpStatusCode
     *
     * @param int $httpStatusCode
     *
     * @return DomainException
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
        return $this;
    }

    /**
     * @param array $details
     * @return self
     */
    public function setAdditionalDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param string $uri
     * @return self
     */
    public function setType($uri)
    {
        $this->type = (string) $uri;
        return $this;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalDetails()
    {
        return $this->details;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
