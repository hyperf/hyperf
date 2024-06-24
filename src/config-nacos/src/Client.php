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

namespace Hyperf\ConfigNacos;

use Hyperf\Codec\Json;
use Hyperf\Codec\Xml;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Exception\RequestException;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Client implements ClientInterface
{
    protected ConfigInterface $config;

    protected Application $client;

    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->client = $container->get(NacosClient::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function getClient(): Application
    {
        return $this->client;
    }

    public function pull(): array
    {
        $listener = $this->config->get('config_center.drivers.nacos.listener_config', []);

        $config = [];
        foreach ($listener as $key => $item) {
            $dataId = $item['data_id'] ?? '';
            $group = $item['group'] ?? '';
            $tenant = $item['tenant'] ?? null;
            $type = $item['type'] ?? null;
            $response = $this->client->config->get($dataId, $group, $tenant);
            if ($response->getStatusCode() !== 200) {
                $this->logger->error(sprintf('The config of %s read failed from Nacos.', $key));
                continue;
            }
            $config[$key] = $this->decode((string) $response->getBody(), $type);
        }

        return $config;
    }

    public function decode(string $body, ?string $type = null): array|string
    {
        $type = strtolower((string) $type);

        return match ($type) {
            'json' => Json::decode($body),
            'yml', 'yaml' => yaml_parse($body),
            'xml' => Xml::toArray($body),
            default => $body,
        };
    }

    public function getValidNodes(
        string $serviceName,
        #[ArrayShape([
            'groupName' => 'string',
            'namespaceId' => 'string',
            'clusters' => 'string', // 集群名称(字符串，多个集群用逗号分隔)
            'healthyOnly' => 'bool',
        ])]
        array $optional = []
    ): array {
        $response = $this->client->instance->list($serviceName, $optional);
        if ($response->getStatusCode() !== 200) {
            throw new RequestException((string) $response->getBody(), $response->getStatusCode());
        }

        $data = Json::decode((string) $response->getBody());
        $hosts = $data['hosts'] ?? [];
        return array_filter($hosts, function ($item) {
            return $item['valid'] ?? false;
        });
    }
}
