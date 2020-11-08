<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\HttpServer;

use Hyperf\Contract\ResponseEmitterInterface;
use Hyperf\HttpMessage\Stream\FileInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @var Response
     */
    protected static $swooleResponse;

    public function emit(ResponseInterface $response, bool $withContent = true)
    {
        if (strtolower(static::$swooleResponse->header['Upgrade'] ?? '') === 'websocket') {
            return;
        }
        $this->buildSwooleResponse($response);
        $content = $response->getBody();
        if ($content instanceof FileInterface) {
            return static::$swooleResponse->sendfile($content->getFilename());
        }


        if ($withContent) {
            $transferEncodingArray = $response->getHeader('Transfer-Encoding');
            if (in_array('chunked', $transferEncodingArray)) {
                if (empty($content->getContents())) {
                    static::$swooleResponse->end();
                } else {
                    static::$swooleResponse->write($content->getContents());
                }
            } else {
                static::$swooleResponse->end($content->getContents());
            }

        } else {
            static::$swooleResponse->end();
        }
    }

    protected function buildSwooleResponse(ResponseInterface $response): void
    {
        /*
         * Headers
         */
        foreach ($response->getHeaders() as $key => $value) {
            static::$swooleResponse->header($key, implode(';', $value));
        }

        /*
         * Cookies
         * This part maybe only supports of hyperf/http-message component.
         */
        if (method_exists($response, 'getCookies')) {
            foreach ((array) $response->getCookies() as $domain => $paths) {
                foreach ($paths ?? [] as $path => $item) {
                    foreach ($item ?? [] as $name => $cookie) {
                        if ($this->isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                            static::$swooleResponse->rawcookie($cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly(), (string) $cookie->getSameSite());
                        }
                    }
                }
            }
        }

        /*
         * Trailers
         */
        if (method_exists($response, 'getTrailers') && method_exists(static::$swooleResponse, 'trailer')) {
            foreach ($response->getTrailers() ?? [] as $key => $value) {
                static::$swooleResponse->trailer($key, $value);
            }
        }

        /*
         * Status code
         */
        static::$swooleResponse->status($response->getStatusCode());
    }

    protected function isMethodsExists(object $object, array $methods): bool
    {
        foreach ($methods as $method) {
            if (! method_exists($object, $method)) {
                return false;
            }
        }
        return true;
    }

    public function setSwooleResponse(Response $response)
    {
        static::$swooleResponse = $response;
    }

}
