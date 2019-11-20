<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ReactiveX;

use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\Contract\MessageBusInterface;
use Rx\DisposableInterface;
use Rx\Notification\OnCompletedNotification;
use Rx\Notification\OnErrorNotification;
use Rx\Notification\OnNextNotification;
use Rx\Subject\Subject;

class IpcSubject implements MessageBusInterface
{
    /**
     * @var Subject
     */
    protected $subject;

    /**
     * @var BroadcasterInterface
     */
    protected $broadcaster;

    /**
     * @var int
     */
    protected $channelId;

    public function __construct(Subject $subject, BroadcasterInterface $broadcaster = null, int $channelId = 1)
    {
        $this->subject = $subject;
        $this->broadcaster = $broadcaster;
        $this->channelId = $channelId;

        Observable::fromEvent(OnPipeMessage::class)
            ->merge(Observable::fromEvent(PipeMessage::class))
            ->filter(function ($event) {
                return $event->data instanceof IpcMessageWrapper
                && $event->data->channelId === $this->channelId;
            })
            ->map(function ($event) {
                return $event->data->data;
            })
            ->dematerialize()
            ->subscribe($this->subject);
    }

    public function __call($method, $arguments)
    {
        return $this->subject->{$method}(...$arguments);
    }

    public function subscribe($onNextOrObserver = null, callable $onError = null, callable $onCompleted = null): DisposableInterface
    {
        return $this->subject->subscribe($onNextOrObserver, $onError, $onCompleted);
    }

    public function dispose()
    {
        return $this->subject->dispose();
    }

    public function onNext($value)
    {
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnNextNotification($value)
        ));
        $this->subject->onNext($value);
    }

    public function onError(\Throwable $exception)
    {
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnErrorNotification($exception)
        ));
        $this->subject->onError($exception);
    }

    public function onCompleted()
    {
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnCompletedNotification()
        ));
        $this->subject->onCompleted();
    }
}
