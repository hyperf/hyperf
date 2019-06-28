<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\CrontabManager;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\BeforeProcessHandle;

class CrontabRegisterListener implements ListenerInterface
{
    /**
     * @var \Hyperf\Crontab\CrontabManager
     */
    protected $crontabManager;

    /**
     * @var \Hyperf\Contract\StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(CrontabManager $crontabManager, StdoutLoggerInterface $logger)
    {
        $this->crontabManager = $crontabManager;
        $this->logger = $logger;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeProcessHandle::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $this->logger->debug('Crontabs are registered.');
        $this->crontabManager->register((new Crontab())->setCommand('echo time();')
            ->setName('echo-time')
            ->setRule('*/1 * * * *'));
        $this->crontabManager->register((new Crontab())->setCommand('echo time();')
            ->setName('echo-time-1')
            ->setRule('*/11 * * * * *'));
        $this->crontabManager->register((new Crontab())->setCommand('echo time();')
            ->setName('echo-time-2')
            ->setRule('*/2 * * * *'));
    }
}
