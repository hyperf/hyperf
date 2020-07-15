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
namespace Hyperf\Framework;

use Psr\EventDispatcher\EventDispatcherInterface as PsrDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

if (interface_exists(EventDispatcherInterface::class)) {
    /**
     * @internal
     */
    class SymfonyEventDispatcher implements EventDispatcherInterface
    {
        /**
         * @var PsrDispatcherInterface
         */
        private $psrDispatcher;

        public function __construct(PsrDispatcherInterface $psrDispatcher)
        {
            $this->psrDispatcher = $psrDispatcher;
        }

        public function dispatch(object $event, string $eventName = null): object
        {
            return $this->psrDispatcher->dispatch($event);
        }
    }
}
