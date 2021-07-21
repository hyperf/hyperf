<?php

declare(strict_types=1);

namespace Hyperf\ConfigNacos;

use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class NacosDriver extends AbstractDriver
{
    /**
     * @var Client
     */
    protected $client;

    protected $driverName = 'nacos';

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(ClientInterface::class);
    }

    protected function updateConfig(array $config)
    {
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config ?? [] as $key => $conf) {
            if (! is_int($key)) {
                $this->setConfig($key, $conf);
            } else {
                if (is_array($conf)) {
                    foreach ($conf as $k => $value) {
                        $this->setConfig($k, $value);
                    }
                } else {
                    $this->setConfig($root, $conf);
                }
            }
        }
    }

    private function setConfig($key, $conf)
    {
        if (is_array($conf) && $this->config->get('config_center.drivers.nacos.merge_mode') === Constants::CONFIG_MERGE_APPEND) {
            $conf = Arr::merge($this->config->get($key, []), $conf);
        }
        $this->config->set($key, $conf);
    }
}
