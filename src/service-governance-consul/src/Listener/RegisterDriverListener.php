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
namespace Hyperf\ServiceGovernanceConsul\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\DriverManager;
use Hyperf\ServiceGovernanceConsul\ConsulDriver;

class RegisterDriverListener implements ListenerInterface
{
    /**
     * @var DriverManager
     */
    protected $driverManager;

    public function __construct(DriverManager $manager)
    {
        $this->driverManager = $manager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $this->driverManager->register('consul', make(ConsulDriver::class));
    }
}
