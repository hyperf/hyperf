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
namespace Hyperf\Nacos\Config;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nacos\Api\NacosConfig;
use Hyperf\Nacos\Model\ConfigModel;
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
     * @var NacosConfig
     */
    protected $client;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->client = $container->get(NacosConfig::class);
    }

    public function pull(): array
    {
        $listenerConfig = $this->config->get('nacos.config.listener_config', []);

        $config = [];
        foreach ($listenerConfig as $item) {
            $model = new ConfigModel($item);
            if ($content = $this->client->get($model)) {
                $configKey = ($item['mapping_path'] ?? '') ?: $item['data_id'];
                $config[$configKey] = $model->parse($content);
            }
        }

        return $config;
    }
}
