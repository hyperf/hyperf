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
use RuntimeException;

class HttpException extends RuntimeException
{
    /**
     * @var int HTTP status
     */
    public $statusCode;

    /**
     * @param int $status HTTP status
     * @param string $message error message
     * @param int $code error code
     */
    public function __construct($status, $message = '', $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        if (is_null($message)) {
            $message = Response::$httpStatuses[$status] ?? '';
        }

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string the user-friendly name of this exception
     */
    public function getName(): string
    {
        if (isset(Response::$httpStatuses[$this->statusCode])) {
            return Response::$httpStatuses[$this->statusCode];
        }

        return 'Error';
    }
}
