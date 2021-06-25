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
namespace Hyperf\ServiceGovernance\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\ConsulGovernance;
use Hyperf\ServiceGovernance\ServiceGovernanceManager;

class RegisterServiceGovernanceListener implements ListenerInterface
{
    /**
     * @var ServiceGovernanceManager
     */
    protected $manager;

    public function __construct(ServiceGovernanceManager $manager)
    {
        $this->manager = $manager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $this->manager->register('consul', make(ConsulGovernance::class));
    }
}
