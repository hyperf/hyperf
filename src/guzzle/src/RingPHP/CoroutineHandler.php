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
namespace Hyperf\Guzzle\RingPHP;

use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Exception\RingException;
use GuzzleHttp\Ring\Future\CompletedFutureArray;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;

/**
 * Http handler that uses Swoole Coroutine as a transport layer.
 */
class CoroutineHandler
{
    protected $options;

    public function __construct($options = [])
    {
        $this->options = $options;
    }

    public function __invoke($request)
    {
        $method = $request['http_method'] ?? 'GET';
        $scheme = $request['scheme'] ?? 'http';
        $ssl = $scheme === 'https';
        $body = $request['body'] ?? '';
        $effectiveUrl = Core::url($request);
        $params = parse_url($effectiveUrl);
        $host = $params['host'];
        if (! isset($params['port'])) {
            $params['port'] = $this->getPort($request, $ssl);
        }
        $port = $params['port'];
        $path = $params['path'] ?? '/';
        if (isset($params['query']) && is_string($params['query'])) {
            $path .= '?' . $params['query'];
        }

        $client = new Client($host, $port, $ssl);
        $client->setMethod($method);
        $client->setData($body);

        // 初始化Headers
        $this->initHeaders($client, $request);
        $settings = $this->getSettings($this->options);

        // 设置客户端参数
        if (! empty($settings)) {
            $client->set($settings);
        }

        $btime = microtime(true);
        $this->execute($client, $path);

        $ex = $this->checkStatusCode($client, $request);
        if ($ex !== true) {
            return $this->getErrorResponse($ex, $btime, $effectiveUrl);
        }

        return $this->getResponse($client, $btime, $effectiveUrl);
    }

    protected function execute(Client $client, $path)
    {
        $client->execute($path);
    }

    protected function getSettings($options): array
    {
        $settings = [];
        if (isset($options['delay'])) {
            Coroutine::sleep((float) $options['delay'] / 1000);
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        }

        return $settings;
    }

    protected function getPort(array $request, bool $ssl = false): int
    {
        if ($port = $request['client']['curl'][CURLOPT_PORT] ?? null) {
            return (int) $port;
        }

        return $ssl ? 443 : 80;
    }

    protected function initHeaders(Client $client, $request)
    {
        $headers = [];
        foreach ($request['headers'] ?? [] as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $clientConfig = $request['client']['curl'] ?? [];
        if (isset($clientConfig[CURLOPT_USERPWD])) {
            $userInfo = $clientConfig[CURLOPT_USERPWD];
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }

        $headers = $this->rewriteHeaders($headers);

        $client->setHeaders($headers);
    }

    protected function rewriteHeaders(array $headers): array
    {
        // Unknown reason, Content-Length will cause 400 some time.
        unset($headers['Content-Length']);
        return $headers;
    }

    protected function getErrorResponse(\Throwable $throwable, $btime, $effectiveUrl)
    {
        return new CompletedFutureArray([
            'curl' => [
                'errno' => 0,
            ],
            'transfer_stats' => [
                'total_time' => microtime(true) - $btime,
            ],
            'effective_url' => $effectiveUrl,
            'body' => '',
            'status' => null,
            'reason' => null,
            'headers' => [],
            'error' => $throwable,
        ]);
    }

    protected function getResponse(Client $client, $btime, $effectiveUrl)
    {
        return new CompletedFutureArray([
            'transfer_stats' => [
                'total_time' => microtime(true) - $btime,
            ],
            'effective_url' => $effectiveUrl,
            'headers' => isset($client->headers) ? $client->headers : [],
            'status' => $client->statusCode,
            'body' => $this->getStream($client->body),
        ]);
    }

    protected function checkStatusCode($client, $request)
    {
        $statusCode = $client->statusCode;
        $errCode = $client->errCode;

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED) {
            return new RingException(sprintf('Connection timed out errCode=%s', $errCode));
        }

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT) {
            return new RingException('Request timed out');
        }

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET) {
            return new RingException('Server reset');
        }

        return true;
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
