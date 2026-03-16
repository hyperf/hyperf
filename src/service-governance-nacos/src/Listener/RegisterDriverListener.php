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

namespace Hyperf\ServiceGovernanceNacos\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\DriverManager;
use Hyperf\ServiceGovernanceNacos\NacosDriver;
use Hyperf\ServiceGovernanceNacos\NacosGrpcDriver;

use function Hyperf\Support\make;

class RegisterDriverListener implements ListenerInterface
{
    public function __construct(protected DriverManager $driverManager, protected ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        if ($this->config->get('services.drivers.nacos.grpc.enable', false)) {
            $this->driverManager->register('nacos', make(NacosGrpcDriver::class));
        } else {
            $this->driverManager->register('nacos', make(NacosDriver::class));
        }
    }
}
