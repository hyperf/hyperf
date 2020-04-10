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

use Psr\EventDispatcher\EventDispatcherInterface as PsrDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

if (interface_exists(SymfonyDispatcherInterface::class)) {
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
            $this->psrDispatcher->dispatch($event);
        }
    }
}
