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

    /**
     * @var bool
     */
    private $isSubscribed;

    public function __construct(Subject $subject, BroadcasterInterface $broadcaster = null, int $channelId = 1)
    {
        $this->subject = $subject;
        $this->broadcaster = $broadcaster;
        $this->channelId = $channelId;
        $this->isSubscribed = false;
    }

    public function __call($method, $arguments)
    {
        $this->init();
        return $this->subject->{$method}(...$arguments);
    }

    /**
     * Lazy initializer to avoid causing circular dependency in event listeners.
     */
    public function init()
    {
        if ($this->isSubscribed === true) {
            return;
        }
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
        $this->isSubscribed = true;
    }

    public function subscribe($onNextOrObserver = null, callable $onError = null, callable $onCompleted = null): DisposableInterface
    {
        $this->init();
        return $this->subject->subscribe($onNextOrObserver, $onError, $onCompleted);
    }

    public function dispose()
    {
        $this->init();
        return $this->subject->dispose();
    }

    public function onNext($value)
    {
        $this->init();
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnNextNotification($value)
        ));
        $this->subject->onNext($value);
    }

    public function onError(\Throwable $exception)
    {
        $this->init();
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnErrorNotification($exception)
        ));
        $this->subject->onError($exception);
    }

    public function onCompleted()
    {
        $this->init();
        $this->broadcaster->broadcast(new IpcMessageWrapper(
            $this->channelId,
            new OnCompletedNotification()
        ));
        $this->subject->onCompleted();
    }
}
