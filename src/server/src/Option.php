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

use function Hyperf\Tappable\tap;

class Option
{
    /**
     * Send Channel Capacity, Only support multiplex server mode.
     */
    protected int $sendChannelCapacity = 0;

    public static function make(array|Option $options): Option
    {
        if ($options instanceof Option) {
            return $options;
        }

        return tap(new self(), function (Option $option) use ($options) {
            $option->setSendChannelCapacity($options['send_channel_capacity'] ?? 0);
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
}
