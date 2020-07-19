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
use Hyperf\Guzzle\ClientFactory;

abstract class AbstractNacos
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function request($method, $uri, array $options = [])
    {
        return $this->client()->request($method, $uri, $options);
    }

    public function getServerUri(): string
    {
        return sprintf(
            '%s:%d',
            $this->config->get('nacos.host', '127.0.0.1'),
            (int) $this->config->get('nacos.port', 8848)
        );
    }

    public function client(): Client
    {
        $factory = $this->container->get(ClientFactory::class);
        $headers['charset'] = $headers['charset'] ?? 'UTF-8';
        return $factory->create([
            'base_uri' => $this->getServerUri(),
            RequestOptions::HEADERS => [
                'charset' => 'UTF-8',
            ],
        ]);
    }
}
