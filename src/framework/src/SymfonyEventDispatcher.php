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

namespace Hyperf\Framework;

use Hyperf\Framework\Exception\NotImplementedException;
use Psr\EventDispatcher\EventDispatcherInterface as PsrDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymfonyEventDispatcher implements SymfonyDispatcherInterface
{
    /**
     * @var PsrDispatcherInterface
     */
    private $psrDispatcher;

    public function __construct(PsrDispatcherInterface $psrDispatcher)
    {
        $this->psrDispatcher = $psrDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        throw new NotImplementedException();
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($event)
    {
        $this->psrDispatcher->dispatch($event);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        throw new NotImplementedException();
    }
}
