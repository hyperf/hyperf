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
namespace Hyperf\ConfigCenter\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use InvalidArgumentException;

/**
 * @deprecated v3.0
 */
class BootApplicationListener implements ListenerInterface
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->config->get('config_center.enable', false)) {
            return;
        }

        $configs = [
            'config_apollo',
            'aliyun_acm',
            'config_etcd',
            'zookeeper',
        ];

        foreach ($configs as $config) {
            if ($this->config->get($config . '.enable', false)) {
                throw new InvalidArgumentException(
                    sprintf('Config [%s] is not supported, please use config_center instead.', $config)
                );
            }
        }
    }
}
