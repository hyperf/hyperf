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

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class NacosDriver extends AbstractDriver
{
    protected string $driverName = 'nacos';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    protected function updateConfig(array $config): void
    {
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config as $key => $conf) {
            if (is_int($key)) {
                $key = $root;
            }
            if (is_array($conf) && $this->config->get('config_center.drivers.nacos.merge_mode') === Constants::CONFIG_MERGE_APPEND) {
                $conf = Arr::merge($this->config->get($key, []), $conf);
            }

            $this->config->set($key, $conf);
        }
    }
}
