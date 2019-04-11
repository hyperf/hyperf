<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpMessage\Server\Concerns;

use Hyperf\Helper\ArrayHelper;
use Hyperf\Helper\JsonHelper;
use Hyperf\HttpMessage\Stream\SwooleStream;

trait InteractsWithInput
{
    /**
     * Retrieve a server variable from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function server(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getServerParams();
        }
        return $this->getServerParams()[$key] ?? $default;
    }

    /**
     * Retrieve a header from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function header(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getHeaders();
        }
        return $this->getHeader($key) ?? $default;
    }

    /**
     * Retrieve a query string from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function query(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getQueryParams();
        }
        return $this->getQueryParams()[$key] ?? $default;
    }

    /**
     * Retrieve a post item from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function post(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getParsedBody();
        }
        return $this->getParsedBody()[$key] ?? $default;
    }

    /**
     * Retrieve a input item from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function input(string $key = null, $default = null)
    {
        $inputs = $this->getQueryParams() + $this->getParsedBody();
        if (is_null($key)) {
            return $inputs;
        }
        return $inputs[$key] ?? $default;
    }

    /**
     * Retrieve a cookie from the request.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function cookie(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getCookieParams();
        }
        return $this->getCookieParams()[$key] ?? $default;
    }

    /**
     * Retrieve raw body from the request.
     *
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function raw($default = null)
    {
        $body = $this->getBody();
        $raw = $default;
        if ($body instanceof SwooleStream) {
            $raw = $body->getContents();
        }
        return $raw;
    }

    /**
     * Retrieve a json format raw body from the request,
     * The Content-Type of request must be equal to 'application/json'
     * When Content-Type is not vaild or can not found the key result,
     * The method will always return $default.
     *
     * @param null|string $key
     * @param null|mixed $default
     * @return array|mixed|string
     */
    public function json(string $key = null, $default = null)
    {
        try {
            $contentType = $this->getHeader('content-type');
            if (! $contentType || \stripos($contentType[0], 'application/json') === false) {
                throw new \InvalidArgumentException(sprintf('Invalid Content-Type of the request, expects %s, %s given', 'application/json', ($contentType ? current($contentType) : 'null')));
            }
            $body = $this->getBody();
            if ($body instanceof SwooleStream) {
                $raw = $body->getContents();
                $decodedBody = JsonHelper::decode($raw, true);
            }
        } catch (\Exception $e) {
            return $default;
        }
        if (is_null($key)) {
            return $decodedBody ?? $default;
        }
        return ArrayHelper::get($decodedBody, $key, $default);
    }

    /**
     * Retrieve a upload item from the request.
     *
     * @param null|string $key
     * @param null $default
     * @return null|array|\Hyperf\Web\UploadedFile
     */
    public function file(string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->getUploadedFiles();
        }
        return $this->getUploadedFiles()[$key] ?? $default;
    }
}
