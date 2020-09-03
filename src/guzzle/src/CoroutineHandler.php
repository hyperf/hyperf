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
namespace Hyperf\Guzzle;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function GuzzleHttp\is_host_in_noproxy;

/**
 * Http handler that uses Swoole Coroutine as a transport layer.
 */
class CoroutineHandler
{
    /**
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'https';
        $path = $uri->getPath();
        $query = $uri->getQuery();

        if (empty($port)) {
            $port = $ssl ? 443 : 80;
        }
        if (empty($path)) {
            $path = '/';
        }
        if ($query !== '') {
            $path .= '?' . $query;
        }

        $client = new Client($host, $port, $ssl);
        $client->setMethod($request->getMethod());
        $client->setData((string) $request->getBody());

        // 初始化Headers
        $this->initHeaders($client, $request, $options);
        // 初始化配置
        $settings = $this->getSettings($request, $options);
        // 设置客户端参数
        if (! empty($settings)) {
            $client->set($settings);
        }

        $ms = microtime(true);

        $this->execute($client, $path);

        $ex = $this->checkStatusCode($client, $request);
        if ($ex !== true) {
            return \GuzzleHttp\Promise\rejection_for($ex);
        }

        $response = $this->getResponse($client, $request, $options, microtime(true) - $ms);

        return new FulfilledPromise($response);
    }

    protected function execute(Client $client, $path)
    {
        $client->execute($path);
    }

    protected function initHeaders(Client $client, RequestInterface $request, $options)
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $userInfo = $request->getUri()->getUserInfo();
        if ($userInfo) {
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }

        $headers = $this->rewriteHeaders($headers);

        $client->setHeaders($headers);
    }

    protected function rewriteHeaders(array $headers): array
    {
        // Unknown reason, Content-Length will cause 400 some time.
        // Expect header is not supported by \Swoole\Coroutine\Http\Client.
        unset($headers['Content-Length'], $headers['Expect']);
        return $headers;
    }

    protected function getSettings(RequestInterface $request, $options): array
    {
        $settings = [];
        if (isset($options['delay']) && $options['delay'] > 0) {
            Coroutine::sleep((float) $options['delay'] / 1000);
        }

        // 验证服务端证书
        if (isset($options['verify'])) {
            if ($options['verify'] === false) {
                $settings['ssl_verify_peer'] = false;
            } else {
                $settings['ssl_verify_peer'] = false;
                $settings['ssl_allow_self_signed'] = true;
                $settings['ssl_host_name'] = $request->getUri()->getHost();
                if (is_string($options['verify'])) {
                    // Throw an error if the file/folder/link path is not valid or doesn't exist.
                    if (! file_exists($options['verify'])) {
                        throw new \InvalidArgumentException("SSL CA bundle not found: {$options['verify']}");
                    }
                    // If it's a directory or a link to a directory use CURLOPT_CAPATH.
                    // If not, it's probably a file, or a link to a file, so use CURLOPT_CAINFO.
                    if (is_dir($options['verify']) ||
                        (is_link($options['verify']) && is_dir(readlink($options['verify'])))) {
                        $settings['ssl_capath'] = $options['verify'];
                    } else {
                        $settings['ssl_cafile'] = $options['verify'];
                    }
                }
            }
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $settings['timeout'] = $options['timeout'];
        }

        // Proxy
        if (isset($options['proxy'])) {
            $uri = null;
            if (is_array($options['proxy'])) {
                $scheme = $request->getUri()->getScheme();
                if (isset($options['proxy'][$scheme])) {
                    $host = $request->getUri()->getHost();
                    if (! isset($options['proxy']['no']) || ! is_host_in_noproxy($host, $options['proxy']['no'])) {
                        $uri = new Uri($options['proxy'][$scheme]);
                    }
                }
            } else {
                $uri = new Uri($options['proxy']);
            }

            if ($uri) {
                $settings['http_proxy_host'] = $uri->getHost();
                $settings['http_proxy_port'] = $uri->getPort();
                if ($uri->getUserInfo()) {
                    [$user, $password] = explode(':', $uri->getUserInfo());
                    $settings['http_proxy_user'] = $user;
                    $settings['http_proxy_password'] = $password;
                }
            }
        }

        // SSL KEY
        isset($options['ssl_key']) && $settings['ssl_key_file'] = $options['ssl_key'];
        isset($options['cert']) && $settings['ssl_cert_file'] = $options['cert'];

        // Swoole Setting
        if (isset($options['swoole']) && is_array($options['swoole'])) {
            $settings = array_replace($settings, $options['swoole']);
        }

        return $settings;
    }

    protected function getResponse(Client $client, RequestInterface $request, array $options, float $transferTime)
    {
        if ($client->set_cookie_headers) {
            $client->headers['set-cookie'] = $client->set_cookie_headers;
        }

        $body = $client->body;
        if (isset($options['sink']) && is_string($options['sink'])) {
            $body = $this->createSink($body, $options['sink']);
        }

        $response = new Psr7\Response(
            $client->statusCode,
            isset($client->headers) ? $client->headers : [],
            $body
        );

        if ($callback = $options[RequestOptions::ON_STATS] ?? null) {
            $stats = new TransferStats(
                $request,
                $response,
                $transferTime,
                $client->errCode,
                []
            );

            $callback($stats);
        }

        return $response;
    }

    protected function createStream(string $body): StreamInterface
    {
        return Psr7\stream_for($body);
    }

    protected function createSink(string $body, string $sink)
    {
        if (! empty($options['stream'])) {
            return $body;
        }

        $stream = fopen($sink, 'w+');
        if ($body !== '') {
            fwrite($stream, $body);
            fseek($stream, 0);
        }

        return $stream;
    }

    protected function checkStatusCode(Client $client, $request)
    {
        $statusCode = $client->statusCode;
        $errCode = $client->errCode;
        $ctx = [
            'statusCode' => $statusCode,
            'errCode' => $errCode,
        ];

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_CONNECT_FAILED) {
            return new ConnectException(sprintf('Connection failed, errCode=%s', $errCode), $request, null, $ctx);
        }

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_REQUEST_TIMEOUT) {
            return new RequestException(sprintf('Request timed out, errCode=%s', $errCode), $request, null, null, $ctx);
        }

        if ($statusCode === SWOOLE_HTTP_CLIENT_ESTATUS_SERVER_RESET) {
            return new RequestException('Server reset', $request, null, null, $ctx);
        }

        return true;
    }
}
