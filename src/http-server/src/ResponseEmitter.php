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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\FileInterface;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
use Throwable;

class ResponseEmitter implements ResponseEmitterInterface
{
    public function __construct(protected ?StdoutLoggerInterface $logger)
    {
    }

    /**
     * @param Response $connection
     */
    public function emit(ResponseInterface $response, mixed $connection, bool $withContent = true): void
    {
        try {
            if (strtolower($connection->header['Upgrade'] ?? '') === 'websocket') {
                return;
            }
            $this->buildSwooleResponse($connection, $response);
            $content = $response->getBody();
            if ($content instanceof FileInterface) {
                $connection->sendfile($content->getFilename());
                return;
            }

            if ($withContent) {
                $connection->end((string) $content);
            } else {
                $connection->end();
            }
        } catch (Throwable $exception) {
            $this->logger?->critical((string) $exception);
        }
    }

    protected function buildSwooleResponse(Response $swooleResponse, ResponseInterface $response): void
    {
        // Headers
        foreach ($response->getHeaders() as $key => $value) {
            $swooleResponse->header($key, $value);
        }

        // Cookies
        // This part maybe only supports of hyperf/http-message component.
        if (method_exists($response, 'getCookies')) {
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

        // Trailers
        if (method_exists($response, 'getTrailers') && method_exists($swooleResponse, 'trailer')) {
            foreach ($response->getTrailers() ?? [] as $key => $value) {
                $swooleResponse->trailer($key, $value);
            }
        }

        // Status code
        $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());
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
}
