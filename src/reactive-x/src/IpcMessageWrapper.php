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

use Rx\Notification;

class IpcMessageWrapper
{
    /**
     * @var Notification
     */
    public $data;

    /**
     * Channel ID.
     *
     * @var int
     */
    public $channelId;

    public function __construct(int $channelId, Notification $data = null)
    {
        $this->channelId = $channelId;
        $this->data = $data;
    }
}
