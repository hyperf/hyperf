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

namespace Hyperf\HttpServer;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method array getServerParams()
 * @method array getCookieParams()
 * @method ServerRequestInterface withCookieParams(array $cookies)
 * @method array getQueryParams()
 * @method ServerRequestInterface withQueryParams(array $query)
 * @method array getUploadedFiles()
 * @method ServerRequestInterface withUploadedFiles(array $uploadedFiles)
 * @method null|null|object getParsedBody()
 * @method ServerRequestInterface withParsedBody($data)
 * @method array getAttributes()
 * @method mixed getAttribute($name, $default = null)
 * @method ServerRequestInterface withAttribute($name, $value)
 * @method ServerRequestInterface withoutAttribute($name)
 */
class Request implements RequestInterface
{
    const CONTEXT_KEY = 'httpRequestParsedData';

    public function __call($name, $arguments)
    {
        $request = $this->getRequest();
        if (! method_exists($request, $name)) {
            throw new \RuntimeException('Method not exist.');
        }
        return $request->$name(...$arguments);
    }

    public function input(?string $key = null, $default = null)
    {
        $data = $this->getInputData();

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * @return []array [found, not-found]
     */
    public function hasInput(array $keys = []): array
    {
        $data = $this->getInputData();
        $found = [];

        foreach ($keys as $key) {
            if (Arr::has($data, $key)) {
                $found[] = $key;
            }
        }

        return [
            $found,
            array_diff($keys, $found),
        ];
    }

    private function getInputData(): array
    {
        return $this->storeParsedData(function () {
            $request = $this->getRequest();
            $contentType = $request->getHeaderLine('Content-Type');
            if ($contentType && Str::startsWith($contentType, 'application/json')) {
                $body = $request->getBody();
                $data = json_decode($body->getContents(), true) ?? [];
            } elseif (is_array($request->getParsedBody())) {
                $data = $request->getParsedBody();
            } else {
                $data = [];
            }

            return array_merge($data, $request->getQueryParams());
        });
    }

    public function header(string $key = null, $default = null)
    {
        return $this->getRequest()->getHeaderLine($key);
    }

    private function getRequest(): ServerRequestInterface
    {
        return Context::get(ServerRequestInterface::class);
    }

    private function storeParsedData(callable $callback)
    {
        if (! Context::has(self::CONTEXT_KEY)) {
            return Context::set(self::CONTEXT_KEY, call($callback));
        }
        return Context::get(self::CONTEXT_KEY);
    }
}
