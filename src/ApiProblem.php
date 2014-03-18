<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\ApiProblem;

/**
 * Object describing an API-Problem payload
 */
class ApiProblem
{
    /**
     * Additional details to include in report
     *
     * @var array
     */
    protected $additionalDetails = array();

    /**
     * URL describing the problem type; defaults to HTTP status codes
     * @var string
     */
    protected $type = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';

    /**
     * Description of the specific problem.
     *
     * @var string|\Exception
     */
    protected $detail = '';

    /**
     * Whether or not to include a stack trace and previous
     * exceptions when an exception is provided for the detail.
     *
     * @var bool
     */
    protected $detailIncludesStackTrace = false;

    /**
     * HTTP status for the error.
     *
     * @var int
     */
    protected $status;

    /**
     * Normalized property names for overloading
     *
     * @var array
     */
    protected $normalizedProperties = array(
        'type'   => 'type',
        'status' => 'status',
        'title'  => 'title',
        'detail' => 'detail',
    );

    /**
     * Status titles for common problems
     *
     * @var array
     */
    protected $problemStatusTitles = array(
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );

    /**
     * Title of the error.
     *
     * @var string
     */
    protected $title;

    /**
     * Constructor
     *
     * Create an instance using the provided information. If nothing is
     * provided for the type field, the class default will be used;
     * if the status matches any known, the title field will be selected
     * from $problemStatusTitles as a result.
     *
     * @param int    $status
     * @param string $detail
     * @param string $type
     * @param string $title
     * @param array  $additional
     */
    public function __construct($status, $detail, $type = null, $title = null, array $additional = array())
    {
        if ($detail instanceof Exception\ProblemExceptionInterface) {
            if (null === $type) {
                $type = $detail->getType();
            }
            if (null === $title) {
                $title = $detail->getTitle();
            }
            if (empty($additional)) {
                $additional = $detail->getAdditionalDetails();
            }
        }

        $this->status = $status;
        $this->detail = $detail;
        $this->title  = $title;

        if (null !== $type) {
            $this->type = $type;
        }

        $this->additionalDetails = $additional;
    }

    /**
     * Retrieve properties
     *
     * @param  string $name
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __get($name)
    {
        $normalized = strtolower($name);
        if (in_array($normalized, array_keys($this->normalizedProperties))) {
            $prop = $this->normalizedProperties[$normalized];
            return $this->{$prop};
        }

        if (isset($this->additionalDetails[$name])) {
            return $this->additionalDetails[$name];
        }

        if (isset($this->additionalDetails[$normalized])) {
            return $this->additionalDetails[$normalized];
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid property name "%s"',
            $name
        ));
    }

    /**
     * Cast to an array
     *
     * @return array
     */
    public function toArray()
    {
        $problem = array(
            'type'   => $this->type,
            'title'  => $this->getTitle(),
            'status' => $this->getStatus(),
            'detail' => $this->getDetail(),
        );
        // Required fields should always overwrite additional fields
        return array_merge($this->additionalDetails, $problem);
    }

    /**
     * Set the flag indicating whether an exception detail should include a
     * stack trace and previous exception information.
     *
     * @param  bool $flag
     * @return ApiProblem
     */
    public function setDetailIncludesStackTrace($flag)
    {
        $this->detailIncludesStackTrace = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve the API-Problem detail
     *
     * If an exception was provided, creates the detail message from it;
     * otherwise, detail as provided is used.
     *
     * @return string
     */
    protected function getDetail()
    {
        if ($this->detail instanceof \Exception) {
            return $this->createDetailFromException();
        }

        return $this->detail;
    }

    /**
     * Retrieve the API-Problem HTTP status code
     *
     * If an exception was provided, creates the status code from it;
     * otherwise, code as provided is used.
     *
     * @return string
     */
    protected function getStatus()
    {
        if ($this->detail instanceof \Exception) {
            $this->status = $this->createStatusFromException();
        }

        return $this->status;
    }

    /**
     * Retrieve the title
     *
     * If the default $type is used, and the $status is found in
     * $problemStatusTitles, then use the matching title.
     *
     * If no title was provided, and the above conditions are not met, use the
     * string 'Unknown'.
     *
     * Otherwise, use the title provided.
     *
     * @return string
     */
    protected function getTitle()
    {
        if (null !== $this->title) {
            return $this->title;
        }

        if (null === $this->title
            && $this->type == 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html'
            && array_key_exists($this->getStatus(), $this->problemStatusTitles)
        ) {
            return $this->problemStatusTitles[$this->status];
        }

        if ($this->detail instanceof \Exception) {
            return get_class($this->detail);
        }

        if (null === $this->title) {
            return 'Unknown';
        }

        return $this->title;
    }

    /**
     * Create detail message from an exception.
     *
     * @return string
     */
    protected function createDetailFromException()
    {
        $e = $this->detail;

        if (!$this->detailIncludesStackTrace) {
            return $e->getMessage();
        }

        $message = trim($e->getMessage());
        $this->additionalDetails['trace'] = $e->getTrace();

        $previous = array();
        $e = $e->getPrevious();
        while ($e) {
            $previous[] = array(
                'code'    => (int) $e->getCode(),
                'message' => trim($e->getMessage()),
                'trace'   => $e->getTrace(),
            );
            $e = $e->getPrevious();
        }
        if (count($previous)) {
            $this->additionalDetails['exception_stack'] = $previous;
        }

        return $message;
    }

    /**
     * Create HTTP status from an exception.
     *
     * @return string
     */
    protected function createStatusFromException()
    {
        $e      = $this->detail;
        $status = $e->getCode();

        if (!empty($status)) {
            return $status;
        } else {
            return 500;
        }
    }
}
