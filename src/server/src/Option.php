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

namespace Hyperf\Server;

use Hyperf\HttpServer\PriorityMiddleware;

use function Hyperf\Tappable\tap;

class Option
{
    /**
     * Send Channel Capacity, Only support multiplex server mode.
     */
    protected int $sendChannelCapacity = 0;

    /**
     * Whether to enable request lifecycle event.
     */
    protected bool $enableRequestLifecycle = false;

    /**
     * Whether to sort middlewares by priority.
     */
    protected bool $mustSortMiddlewares = false;

    public static function make(array|Option $options): Option
    {
        if ($options instanceof Option) {
            return $options;
        }

        return tap(new self(), function (Option $option) use ($options) {
            $option->setSendChannelCapacity($options['send_channel_capacity'] ?? 0);
            $option->setEnableRequestLifecycle($options['enable_request_lifecycle'] ?? false);
        });
    }

    public function getSendChannelCapacity(): int
    {
        return $this->sendChannelCapacity;
    }

    public function setSendChannelCapacity(int $sendChannelCapacity): static
    {
        $this->sendChannelCapacity = $sendChannelCapacity;
        return $this;
    }

    public function isEnableRequestLifecycle(): bool
    {
        return $this->enableRequestLifecycle;
    }

    public function setEnableRequestLifecycle(bool $enableRequestLifecycle): static
    {
        $this->enableRequestLifecycle = $enableRequestLifecycle;
        return $this;
    }

    public function isMustSortMiddlewares(): bool
    {
        return $this->mustSortMiddlewares;
    }

    public function setMustSortMiddlewares(bool $mustSortMiddlewares): static
    {
        $this->mustSortMiddlewares = $mustSortMiddlewares;
        return $this;
    }

    public function setMustSortMiddlewaresByMiddlewares(array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            if (is_int($middleware) || $middleware instanceof PriorityMiddleware) {
                return $this->setMustSortMiddlewares(true);
            }
        }
        return $this;
    }
}
