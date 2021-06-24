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
use Hyperf\ConfigNacos\Config\PipeMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\ProcessCollector;
use Hyperf\Process\ProcessManager;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Coordinator\Constants as CooConst;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class NacosDriver extends AbstractDriver
{
    /**
     * @var Client
     */
    protected $client;

    protected $driverName = 'nacos';

    protected $pipeMessage = PipeMessage::class;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->client = $container->get(Client::class);
    }

    protected function updateConfig(array $config)
    {
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config ?? [] as $key => $conf) {
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
