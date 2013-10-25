<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem\Exception;

/**
 * Interface for exceptions that can provide additional API Problem details
 */
interface ProblemExceptionInterface
{
    /**
     * @return null|array|\Traversable
     */
    public function getAdditionalDetails();

    /**
     * @return string
     */
    public function getProblemType();

    /**
     * @return string
     */
    public function getTitle();
}
