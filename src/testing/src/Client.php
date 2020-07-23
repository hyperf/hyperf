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
namespace Hyperf\Testing;

use Hyperf\Contract\PackerInterface;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\MiddlewareManager;
use Hyperf\HttpServer\ResponseEmitter;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Server;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Context;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Utils\Packer\JsonPacker;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Coroutine as SwCoroutine;

class Client extends Server
{
    /**
     * @var array
     */
    public $ignoreContextPrefix = [
        'database.connection',
        'redis.connection',
    ];

    /**
     * @var PackerInterface
     */
    protected $packer;

    public function __construct(ContainerInterface $container, PackerInterface $packer = null, $server = 'http')
    {
        parent::__construct($container, $container->get(HttpDispatcher::class), $container->get(ExceptionHandlerDispatcher::class), $container->get(ResponseEmitter::class));
        $this->packer = $packer ?? new JsonPacker();

        $this->initCoreMiddleware($server);
    }

    public function get($uri, $data = [], $headers = [])
    {
        $response = $this->request('GET', $uri, [
            'headers' => $headers,
            'query' => $data,
        ]);

        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function post($uri, $data = [], $headers = [])
    {
        $response = $this->request('POST', $uri, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function put($uri, $data = [], $headers = [])
    {
        $response = $this->request('PUT', $uri, [
            'headers' => $headers,
            'form_params' => $data,
        ]);

        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function delete($uri, $data = [], $headers = [])
    {
        $response = $this->request('DELETE', $uri, [
            'headers' => $headers,
            'query' => $data,
        ]);

        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function json($uri, $data = [], $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        $response = $this->request('POST', $uri, [
            'headers' => $headers,
            'json' => $data,
        ]);
        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function file($uri, $data = [], $headers = [])
    {
        $multipart = [];
        if (Arr::isAssoc($data)) {
            $data = [$data];
        }

        foreach ($data as $item) {
            $name = $item['name'];
            $file = $item['file'];

            $multipart[] = [
                'name' => $name,
                'contents' => fopen($file, 'r'),
                'filename' => basename($file),
            ];
        }

        $response = $this->request('POST', $uri, [
            'headers' => $headers,
            'multipart' => $multipart,
        ]);

        return $this->packer->unpack($response->getBody()->getContents());
    }

    public function request(string $method, string $path, array $options = [])
    {
        /*
         * @var Psr7Request
         */
        [$psr7Request, $psr7Response] = $this->init($method, $path, $options);

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);
        /** @var Dispatched $dispatched */
        $dispatched = $psr7Request->getAttribute(Dispatched::class);
        $middlewares = $this->middlewares;
        if ($dispatched->isFound()) {
            $registedMiddlewares = MiddlewareManager::get($this->serverName, $dispatched->handler->route, $psr7Request->getMethod());
            $middlewares = array_merge($middlewares, $registedMiddlewares);
        }

        try {
            $psr7Response = $this->dispatcher->dispatch($psr7Request, $middlewares, $this->coreMiddleware);
        } catch (\Throwable $throwable) {
            // Delegate the exception to exception handler.
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($throwable, $this->exceptionHandlers);
        }

        return $psr7Response;
    }

    protected function init(string $method, string $path, array $options = []): array
    {
        $this->flushContext();

        $query = $options['query'] ?? [];
        $params = $options['form_params'] ?? [];
        $json = $options['json'] ?? [];
        $headers = $options['headers'] ?? [];
        $multipart = $options['multipart'] ?? [];

        $data = $params;

        // Initialize PSR-7 Request and Response objects.
        $uri = (new Uri())->withPath($path)->withQuery(http_build_query($query));

        $content = http_build_query($params);
        if ($method == 'POST' && data_get($headers, 'Content-Type') == 'application/json') {
            $content = json_encode($json, JSON_UNESCAPED_UNICODE);
            $data = $json;
        }

        $body = new SwooleStream($content);

        $request = new Psr7Request($method, $uri, $headers, $body);
        $request = $request->withQueryParams($query)
            ->withParsedBody($data)
            ->withUploadedFiles($this->normalizeFiles($multipart));

        Context::set(ServerRequestInterface::class, $psr7Request = $request);
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());

        return [$psr7Request, $psr7Response];
    }

    protected function flushContext()
    {
        $context = SwCoroutine::getContext() ?? [];

        foreach ($context as $key => $value) {
            if (Str::startsWith($key, $this->ignoreContextPrefix)) {
                continue;
            }
            $context[$key] = null;
        }
    }

    protected function normalizeFiles(array $multipart): array
    {
        $files = [];
        $fileSystem = $this->container->get(Filesystem::class);

        foreach ($multipart as $item) {
            if (isset($item['name'], $item['contents'], $item['filename'])) {
                $name = $item['name'];
                $contents = $item['contents'];
                $filename = $item['filename'];

                $dir = BASE_PATH . '/runtime/uploads';
                $tmpName = $dir . '/' . $filename;
                $fileSystem->makeDirectory($dir);
                $fileSystem->put($tmpName, $contents);

                $stats = fstat($contents);

                $files[$name] = new UploadedFile(
                    $tmpName,
                    $stats['size'],
                    0,
                    $name
                );
            }
        }

        return $files;
    }

    protected function getStream(string $resource)
    {
        $stream = fopen('php://temp', 'r+');
        if ($resource !== '') {
            fwrite($stream, $resource);
            fseek($stream, 0);
        }

        return $stream;
    }
}
