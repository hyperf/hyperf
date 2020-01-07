<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpAuth\Exception;

use Hyperf\Server\Exception\ServerException;

class UnauthorizedHttpException extends ServerException
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $challenge WWW-Authenticate challenge string
     * @param string $message The internal exception message
     * @param \Throwable $previous The previous exception
     * @param int $code The internal exception code
     */
    public function __construct(string $challenge, string $message = null, \Throwable $previous = null, ?int $code = 0, array $headers = [])
    {
        $headers['WWW-Authenticate'] = $challenge;

        $this->statusCode = 401;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set response headers.
     *
     * @param array $headers Response headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
