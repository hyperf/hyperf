<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpMessage\Exception;

use Hyperf\HttpMessage\Server\Response;
use Hyperf\Server\Exception\ServerException;

class HttpException extends ServerException
{
    /**
     * @var int HTTP status code, such as 403, 404, 500, etc
     */
    public $statusCode;

    /**
     * Constructor.
     * @param int $status HTTP status code, such as 404, 500, etc
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($status, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        if (isset(Response::$httpStatuses[$this->statusCode])) {
            return Response::$httpStatuses[$this->statusCode];
        }

        return 'Error';
    }
}
