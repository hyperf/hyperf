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
namespace Hyperf\GuzzleHandler;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;

/**
 * Http handler that uses Swoole Coroutine as a transport layer.
 */
class CoroutineHandler
{
    /**
     * Swoole 协程 Http 客户端
     *
     * @var \Swoole\Coroutine\Http\Client
     */
    private $client;

    /**
     * 配置选项
     *
     * @var array
     */
    private $settings = [];

    /**
     * @author limx
     * @param RequestInterface $request
     * @param array            $options
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = 'https' === $uri->getScheme();
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

        $this->client = new Client($host, $port, $ssl);
        $this->client->setMethod($request->getMethod());
        $this->client->setData((string)$request->getBody());

        // 初始化Headers
        $this->initHeaders($request, $options);
        // 初始化配置
        $this->initSettings($request, $options);
        // 设置客户端参数
        if (!empty($this->settings)) {
            $this->client->set($this->settings);
        }
        $this->client->execute($path);
        $ex = $this->checkStatusCode($request);
        if ($ex !== true) {
            return \GuzzleHttp\Promise\rejection_for($ex);
        }

        $response = $this->getResponse();

        return new FulfilledPromise($response);
    }

    protected function initHeaders(RequestInterface $request, $options)
    {
        $headers = [];
        foreach ($request->getHeaders() as $name => $value) {
            $headers[$name] = implode(',', $value);
        }

        $userInfo = $request->getUri()->getUserInfo();
        if ($userInfo) {
            $headers['Authorization'] = sprintf('Basic %s', base64_encode($userInfo));
        }

        // TODO: 不知道为啥，这个扔进来就400
        unset($headers['Content-Length']);
        $this->client->setHeaders($headers);
    }

    protected function initSettings(RequestInterface $request, $options)
    {
        if (isset($options['delay'])) {
            Coroutine::sleep((float)$options['delay'] / 1000);
        }

        // 验证服务端证书
        if (isset($options['verify'])) {
            if ($options['verify'] === false) {
                $this->settings['ssl_verify_peer'] = false;
            } else {
                $this->settings['ssl_verify_peer'] = false;
                $this->settings['ssl_allow_self_signed'] = true;
                $this->settings['ssl_host_name'] = $request->getUri()->getHost();
                if (is_string($options['verify'])) {
                    // Throw an error if the file/folder/link path is not valid or doesn't exist.
                    if (!file_exists($options['verify'])) {
                        throw new \InvalidArgumentException("SSL CA bundle not found: {$options['verify']}");
                    }
                    // If it's a directory or a link to a directory use CURLOPT_CAPATH.
                    // If not, it's probably a file, or a link to a file, so use CURLOPT_CAINFO.
                    if (is_dir($options['verify']) ||
                        (is_link($options['verify']) && is_dir(readlink($options['verify'])))) {
                        $this->settings['ssl_capath'] = $options['verify'];
                    } else {
                        $this->settings['ssl_cafile'] = $options['verify'];
                    }
                }
            }
        }

        // 超时
        if (isset($options['timeout']) && $options['timeout'] > 0) {
            $this->settings['timeout'] = $options['timeout'];
        }
    }

    protected function getResponse()
    {
        $response = new \GuzzleHttp\Psr7\Response(
            $this->client->statusCode,
            isset($this->client->headers) ? $this->client->headers : [],
            $this->client->body
        );
        return $response;
    }

    protected function checkStatusCode($request)
    {
        $statusCode = $this->client->statusCode;
        $errCode = $this->client->errCode;
        $ctx = [
            'statusCode' => $statusCode,
            'errCode' => $errCode,
        ];
        if ($statusCode === -1) {
            return new ConnectException(sprintf('Connection timed out errCode=%s', $errCode), $request, null, $ctx);
        } elseif ($statusCode === -2) {
            return new RequestException('Request timed out', $request, null, null, $ctx);
        }

        return true;
    }
}
