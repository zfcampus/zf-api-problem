<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiProblem\Exception;

class DomainException extends \DomainException implements
    ExceptionInterface,
    ProblemExceptionInterface
{
    /**
     * @var string
     */
    protected $problemType;

    /**
     * @var array
     */
    protected $details = array();

    /**
     * @var string
     */
    protected $title;

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
    public function setProblemType($uri)
    {
        $this->problemType = (string) $uri;
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
    public function getProblemType()
    {
        return $this->problemType;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
