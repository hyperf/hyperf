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
namespace Hyperf\Utils\Channel;

use Hyperf\Engine\Channel;

class ChannelManager
{
    /**
     * @var Channel[]
     */
    protected $channels = [];

    /**
     * @var int
     */
    protected $size = 1;

    public function __construct(int $size = 1)
    {
        $this->size = $size;
    }

    public function get(int $id, bool $initialize = false): ?Channel
    {
        if (isset($this->channels[$id])) {
            return $this->channels[$id];
        }

        if ($initialize) {
            return $this->channels[$id] = $this->make($this->size);
        }

        return null;
    }

    public function make(int $limit): Channel
    {
        return new Channel($limit);
    }

    public function close(int $id): void
    {
        if ($channel = $this->channels[$id] ?? null) {
            $channel->close();
        }

        unset($this->channels[$id]);
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function flush(): void
    {
        $channels = $this->getChannels();
        foreach ($channels as $id => $channel) {
            $this->close($id);
        }
    }
}
