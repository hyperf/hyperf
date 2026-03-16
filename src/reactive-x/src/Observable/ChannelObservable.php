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

namespace Hyperf\ReactiveX\Observable;

use Hyperf\Engine\Channel;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Scheduler;
use Rx\SchedulerInterface;
use Throwable;

class ChannelObservable extends Observable
{
    public function __construct(private Channel $channel, private ?SchedulerInterface $scheduler = null)
    {
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $action = function ($reschedule) use (&$observer) {
            try {
                $result = $this->channel->pop();
                if ($result === false) {
                    $observer->onCompleted();
                }
                $observer->onNext($result);
                $reschedule();
            } catch (Throwable $e) {
                $observer->onError($e);
            }
        };
        if ($this->scheduler === null) {
            $this->scheduler = Scheduler::getDefault();
        }
        return $this->scheduler->scheduleRecursive($action);
    }
}
