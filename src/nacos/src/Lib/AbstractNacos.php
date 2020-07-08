<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nacos\Lib;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;

abstract class AbstractNacos
{
    /**
     * @var array
     */
    protected $baseInfo = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->baseInfo = $container->get(ConfigInterface::class)->get('nacos', []);
    }

    public function request($method, $uri, array $options = [])
    {
        return $this->client()->request($method, $uri, $options);
    }

    public function getServerUri()
    {
        return $this->baseInfo['host'] . ':' . $this->baseInfo['port'];
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
