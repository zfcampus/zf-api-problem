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
     * @return mixed
     */
    public function getAdditionalDetails();

    /**
     * @return mixed
     */
    public function getProblemType();

    /**
     * @return mixed
     */
    public function getTitle();
}
