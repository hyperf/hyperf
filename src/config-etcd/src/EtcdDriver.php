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
namespace Hyperf\ConfigEtcd;

use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Contract\PackerInterface;
use Psr\Container\ContainerInterface;

class EtcdDriver extends AbstractDriver
{
    protected PackerInterface $packer;

    protected array $mapping = [];

    protected string $driverName = 'etcd';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
        $this->mapping = $this->config->get('config_center.drivers.etcd.mapping', []);
        $this->packer = $container->get($this->config->get('config_center.drivers.etcd.packer', JsonPacker::class));
    }

    protected function updateConfig(array $config): void
    {
        $configurations = $this->format($config);
        foreach ($configurations as $kv) {
            $key = $this->mapping[$kv->key] ?? null;
            if (is_string($key)) {
                $this->config->set($key, $this->packer->unpack($kv->value));
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }

    /**
     * Format kv configurations.
     */
    protected function format(array $config): array
    {
        $result = [];
        foreach ($config as $value) {
            $result[] = new KV($value);
        }

        return $result;
    }
}
