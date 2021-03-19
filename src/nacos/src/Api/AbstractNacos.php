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
namespace Hyperf\Nacos\Api;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Guzzle\CoroutineHandler;

abstract class AbstractNacos
{
    use AccessToken;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var callable
     */
    protected $handler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->handler = new CoroutineHandler();
    }

    public function request($method, $uri, array $options = [])
    {
        $token = $this->getAccessToken();
        $token && $options[RequestOptions::QUERY]['accessToken'] = $token;
        return $this->client()->request($method, $uri, $options);
    }

    public function getServerUri(): string
    {
        $url = $this->config->get('nacos.url');

        if ($url) {
            return $url;
        }

        return sprintf(
            '%s:%d',
            $this->config->get('nacos.host', '127.0.0.1'),
            (int) $this->config->get('nacos.port', 8848)
        );
    }

    public function client(): Client
    {
        return new Client([
            'base_uri' => $this->getServerUri(),
            'handler' => $this->handler,
            RequestOptions::HEADERS => [
                'charset' => 'UTF-8',
            ],
        ]);
    }
}
