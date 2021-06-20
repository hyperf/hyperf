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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\NacosSdk\Application;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Codec\Xml;
use Psr\Container\ContainerInterface;

class Client
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Application
     */
    protected $client;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->client = $container->get(Application::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function pull(): array
    {
        $listener = $this->config->get('nacos.config.listener_config', []);

        $config = [];
        foreach ($listener as $key => $item) {
            $dataId = $item['data_id'];
            $group = $item['group'];
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

    /**
     * @return array|string
     */
    public function decode(string $body, ?string $type = null)
    {
        $type = strtolower((string) $type);
        switch ($type) {
            case 'json':
                return Json::decode($body);
            case 'yml':
            case 'yaml':
                return yaml_parse($body);
            case 'xml':
                return Xml::toArray($body);
            default:
                return $body;
        }
    }
}
