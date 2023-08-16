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
namespace Hyperf\Nacos;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Hyperf\Codec\Json;
use Hyperf\Nacos\Exception\RequestException;
use Hyperf\Nacos\Provider\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractProvider
{
    use AccessToken;

    public function __construct(protected Application $app, protected Config $config)
    {
    }

    public function request(string $method, string|UriInterface $uri, array $options = [])
    {
        if ($accessKey = $this->config->getAccessKey()) {
            $accessSecret = $this->config->getAccessSecret();

            if (str_contains($uri, '/ns/')) { // naming
                $options[RequestOptions::HEADERS]['ak'] = $accessKey;
                $signHeaders = $this->getNamingSignHeaders(
                    $options[RequestOptions::QUERY]['groupName'] ?? '',
                    $options[RequestOptions::QUERY]['serviceName'] ?? '',
                    $accessSecret
                );
                foreach ($signHeaders as $header => $value) {
                    $options[RequestOptions::HEADERS][$header] = $value;
                }
            } else { // config
                $options[RequestOptions::HEADERS]['Spas-AccessKey'] = $accessKey;
                $signHeaders = $this->getMseSignHeaders($options[RequestOptions::QUERY] ?? [], $accessSecret);

                foreach ($signHeaders as $header => $value) {
                    $options[RequestOptions::HEADERS][$header] = $value;
                }
            }
        } else {
            if ($token = $this->getAccessToken()) {
                $options[RequestOptions::QUERY]['accessToken'] = $token;
            }
        }

        return $this->client()->request($method, $uri, $options);
    }

    public function client(): Client
    {
        $config = array_merge($this->config->getGuzzleConfig(), [
            'base_uri' => $this->config->getBaseUri(),
        ]);

        return new Client($config);
    }

    protected function checkResponseIsOk(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return (string) $response->getBody() === 'ok';
    }

    protected function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $contents = (string) $response->getBody();

        if ($statusCode !== 200) {
            throw new RequestException($contents, $statusCode);
        }

        return Json::decode($contents);
    }

    protected function filter(array $input): array
    {
        $result = [];

        foreach ($input as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function getMseSignHeaders(array $data, string $secretKey): array
    {
        $group = $data['group'] ?? '';
        $tenant = $data['tenant'] ?? '';
        $timeStamp = round(microtime(true) * 1000);
        $signStr = '';

        if ($tenant) {
            $signStr .= "{$tenant}+";
        }

        if ($group) {
            $signStr .= "{$group}+";
        }

        $signStr .= "{$timeStamp}";

        return [
            'timeStamp' => $timeStamp,
            'Spas-Signature' => base64_encode(hash_hmac('sha1', $signStr, $secretKey, true)),
        ];
    }

    protected function getNamingSignHeaders(string $groupName, string $serverName, string $secretKey): array
    {
        $timeStamp = round(microtime(true) * 1000);
        $signStr = $timeStamp;

        if (! empty($serverName)) {
            if (str_contains($serverName, '@@') || empty($groupName)) {
                $signStr .= "@@{$serverName}";
            } else {
                $signStr .= "@@{$groupName}@@{$serverName}";
            }
        }

        return [
            'data' => $signStr,
            'signature' => base64_encode(hash_hmac('sha1', $signStr, $secretKey, true)),
        ];
    }
}
