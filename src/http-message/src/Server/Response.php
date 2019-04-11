<?php

namespace Hyperf\Http\Message\Server;

use Hyperf\Contract\Arrayable;
use Hyperf\Helper\JsonHelper;
use Hyperf\Helper\StringHelper;
use Hyperf\Http\Message\Cookie\Cookie;
use Hyperf\Http\Message\Stream\SwooleStream;

/**
 * 响应response
 *
 * @uses      Response
 * @version   2017年05月11日
 * @author    stelin <phpcrazy@126.com>
 * @copyright Copyright 2010-2016 Hyperf software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class Response extends \Hyperf\Http\Message\Base\Response
{
    /**
     * @var \Throwable|null
     */
    protected $exception;

    /**
     * swoole响应请求
     *
     * @var \Swoole\Http\Response
     */
    protected $swooleResponse;

    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * 初始化响应请求
     *
     * @param \Swoole\Http\Response $response
     */
    public function __construct(\Swoole\Http\Response $response)
    {
        $this->swooleResponse = $response;
    }

    /**
     * Redirect to a URL
     *
     * @param string   $url
     * @param null|int $status
     * @return static
     */
    public function redirect($url, $status = 302)
    {
        $response = $this;
        $response = $response->withAddedHeader('Location', (string)$url)->withStatus($status);
        return $response;
    }

    /**
     * return a Raw format response
     *
     * @param  string $data   The data
     * @param  int    $status The HTTP status code.
     * @return \Hyperf\Http\Message\Server\Response when $data not jsonable
     */
    public function raw(string $data = '', int $status = 200): Response
    {
        $response = $this;

        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'text/plain');
        $this->getCharset() && $response = $response->withCharset($this->getCharset());

        // Content
        $data && $response = $response->withContent($data);

        // Status code
        $status && $response = $response->withStatus($status);

        return $response;
    }

    /**
     * return a Json format response
     *
     * @param  array|Arrayable $data            The data
     * @param  int             $status          The HTTP status code.
     * @param  int             $encodingOptions Json encoding options
     * @return static when $data not jsonable
     * @throws \InvalidArgumentException
     */
    public function json($data = [], int $status = 200, int $encodingOptions = JSON_UNESCAPED_UNICODE): Response
    {
        $response = $this;

        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'application/json');
        $this->getCharset() && $response = $response->withCharset($this->getCharset());

        // Content
        if ($data && ($this->isArrayable($data) || is_string($data))) {
            is_string($data) && $data = ['data' => $data];
            $content = JsonHelper::encode($data, $encodingOptions);
            $response = $response->withContent($content);
        } else {
            $response = $response->withContent('{}');
        }

        // Status code
        $status && $response = $response->withStatus($status);


        return $response;
    }

    /**
     * 处理 Response 并发送数据
     */
    public function send()
    {
        $response = $this;

        /**
         * Headers
         */
        // Write Headers to swoole response
        foreach ($response->getHeaders() as $key => $value) {
            $this->swooleResponse->header($key, implode(';', $value));
        }

        /**
         * Cookies
         */
        foreach ((array)$this->cookies as $domain => $paths) {
            foreach ($paths ?? [] as $path => $item) {
                foreach ($item ?? [] as $name => $cookie) {
                    if ($cookie instanceof Cookie) {
                        $this->swooleResponse->cookie($cookie->getName(), $cookie->getValue() ? : 1, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
                    }
                }
            }
        }

        /**
         * Status code
         */
        $this->swooleResponse->status($response->getStatusCode());

        /**
         * Body
         */
        $this->swooleResponse->end($response->getBody()->getContents());
    }

    /**
     * 设置Body内容，使用默认的Stream
     *
     * @param string $content
     * @return static
     */
    public function withContent($content): Response
    {
        if ($this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = new SwooleStream($content);
        return $new;
    }

    /**
     * Return an instance with specified cookies.
     *
     * @param Cookie $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        $clone = clone $this;
        $clone->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        return $clone;
    }

    /**
     * @return null|\Throwable
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param \Throwable $exception
     * @return $this
     */
    public function setException(\Throwable $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function isArrayable($value): bool
    {
        return is_array($value) || $value instanceof Arrayable;
    }

    /**
     * @param string $accept
     * @param string $keyword
     * @return bool
     */
    public function isMatchAccept(string $accept, string $keyword): bool
    {
        return StringHelper::contains($accept, $keyword) === true;
    }

    /**
     * @return \Swoole\Http\Response
     */
    public function getSwooleResponse(): \Swoole\Http\Response
    {
        return $this->swooleResponse;
    }

    /**
     * @param \Swoole\Http\Response $swooleResponse
     * @return $this
     */
    public function setSwooleResponse(\Swoole\Http\Response $swooleResponse)
    {
        $this->swooleResponse = $swooleResponse;
        return $this;
    }
}
