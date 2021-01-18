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
use Hyperf\Engine\Http\Client;
use Hyperf\Engine\Http\RawResponse;
use Hyperf\Utils\Resource;

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

        $client = $this->makeClient($host, $port, $ssl);
        // Init Headers
        $headers = $this->initHeaders($request);
        $settings = $this->getSettings($this->options);
        if (! empty($settings)) {
            $client->set($settings);
        }

        $btime = microtime(true);

        try {
            $raw = $client->request($method, $path, $headers, (string) $body);
        } catch (\Exception $exception) {
            $exception = new RingException($exception->getMessage());
            return $this->getErrorResponse($exception, $btime, $effectiveUrl);
        }

        return $this->getResponse($raw, $btime, $effectiveUrl);
    }

    protected function makeClient(string $host, int $port, bool $ssl): Client
    {
        return new Client($host, $port, $ssl);
    }

    protected function getSettings($options): array
    {
        $settings = [];
        if (isset($options['delay']) && $options['delay'] > 0) {
            usleep(intval($options['delay'] * 1000));
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

    protected function initHeaders($request)
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

        return $this->rewriteHeaders($headers);
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

    protected function getResponse(RawResponse $response, $btime, $effectiveUrl)
    {
        return new CompletedFutureArray([
            'transfer_stats' => [
                'total_time' => microtime(true) - $btime,
            ],
            'effective_url' => $effectiveUrl,
            'headers' => $response->headers,
            'status' => $response->statusCode,
            'body' => Resource::from($response->body),
        ]);
    }
}
