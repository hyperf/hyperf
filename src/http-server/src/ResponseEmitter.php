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
namespace Hyperf\HttpServer;

use Hyperf\HttpMessage\Stream\FileInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseEmitter
{
    public function emit(...$parameters)
    {
        if (count($parameters) < 3) {
            throw new \InvalidArgumentException('Nothing to emit.');
        }
        $response = current($parameters);
        if (! $response instanceof ResponseInterface) {
            throw new \InvalidArgumentException(sprintf('The first parameter of %s should instead of %s', static::class, ResponseInterface::class));
        }

        $swooleResponse = $parameters[1] ?? null;
        if (! $swooleResponse instanceof \Swoole\Http\Response) {
            return;
        }

        $this->buildSwooleResponse($swooleResponse, $response);
        $content = $response->getBody();
        if ($content instanceof FileInterface) {
            return $swooleResponse->sendfile($content->getFilename());
        }
        $withContent = $parameters[2] ?? false;
        if ($withContent) {
            $swooleResponse->end($content->getContents());
        } else {
            $swooleResponse->end();
        }
    }

    protected function buildSwooleResponse(\Swoole\Http\Response $swooleResponse, ResponseInterface $response): void
    {
        /*
         * Headers
         */
        foreach ($response->getHeaders() as $key => $value) {
            $swooleResponse->header($key, implode(';', $value));
        }

        /*
         * Cookies
         * This part maybe only supports of hyperf/http-message component.
         */
        if ($this->isMethodsExists($response, ['getCookies'])) {
            foreach ((array) $response->getCookies() as $domain => $paths) {
                foreach ($paths ?? [] as $path => $item) {
                    foreach ($item ?? [] as $name => $cookie) {
                        if ($this->isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                            $swooleResponse->rawcookie($cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly(), (string) $cookie->getSameSite());
                        }
                    }
                }
            }
        }

        /*
         * Status code
         */
        $swooleResponse->status($response->getStatusCode());
    }

    protected function isMethodsExists(object $object, array $methods): bool
    {
        foreach ($methods as $method) {
            if (! method_exists($object, $method)) {
                return true;
            }
        }
        return true;
    }
}
