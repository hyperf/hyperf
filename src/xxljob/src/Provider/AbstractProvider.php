<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Hyperf\XxlJob\Config;

abstract class AbstractProvider
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function request($method, $uri, array $options = [])
    {
        $token = $this->config->getAccessToken();
        $uri = $this->config->getServerUrlPrefix() . $uri;
        $token && $options[RequestOptions::HEADERS]['XXL-JOB-ACCESS-TOKEN'] = $token;
        return $this->client()->request($method, $uri, $options);
    }

    public function client(): Client
    {
        $config = array_merge($this->config->getGuzzleConfig(), [
            'base_uri' => $this->config->getBaseUri(),
        ]);
        return new Client($config);
    }
}
