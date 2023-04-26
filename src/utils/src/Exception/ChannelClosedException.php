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
namespace Hyperf\Utils\Exception;

class_alias(\Hyperf\Coroutine\Exception\ChannelClosedException::class, ChannelClosedException::class);

if (! class_exists(ChannelClosedException::class)) {
    /**
     * @deprecated since 3.1, use Hyperf\Coroutine\Exception\ChannelClosedException instead.
     */
    class ChannelClosedException extends \Hyperf\Coroutine\Exception\ChannelClosedException
    {
    }
}
