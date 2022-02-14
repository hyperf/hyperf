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
namespace Hyperf\SocketIOServer\Room;

use Hyperf\SocketIOServer\Emitter\Flagger;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\WebSocketServer\Sender;

class MemoryAdapter implements AdapterInterface
{
    use Flagger;

    protected array $rooms = [];

    protected array $sids = [];

    public function __construct(private Sender $sender, private SidProviderInterface $sidProvider)
    {
    }

    public function add(string $sid, string ...$rooms)
    {
        $this->sids[$sid] ??= [];
        foreach ($rooms as $room) {
            $this->sids[$sid][$room] = true;
            $this->rooms[$room] ??= make(MemoryRoom::class);
            $this->rooms[$room]->add($sid);
        }
    }

    public function del(string $sid, string ...$rooms)
    {
        if (count($rooms) === 0) {
            $clientRooms = $this->clientRooms($sid);
            if (empty($clientRooms)) {
                return;
            }
            $this->del($sid, ...$clientRooms);
            unset($this->sids[$sid]);
        }

        foreach ($rooms as $room) {
            if (isset($this->sids[$sid])) {
                unset($this->sids[$sid][$room]);
            }
            if (isset($this->rooms[$room])) {
                $this->rooms[$room]->del($sid);
                if ($this->rooms[$room]->size() === 0) {
                    unset($this->rooms[$room]);
                }
            }
        }
    }

    public function broadcast($packet, $opts)
    {
        $rooms = data_get($opts, 'rooms', []);
        $except = data_get($opts, 'except', []);
        $volatile = data_get($opts, 'flag.volatile', false);
        $compress = data_get($opts, 'flag.compress', false);
        $wsFlag = $this->guessFlags((bool) $compress);
        $pushed = [];
        if (! empty($rooms)) {
            foreach ($rooms as $room) {
                $room = $this->rooms[$room] ?? null;
                if (! $room) {
                    continue;
                }
                foreach ($room->list() as $sid) {
                    $sid = strval($sid);
                    if (in_array($sid, $except)) {
                        continue;
                    }
                    $fd = $this->sidProvider->getFd($sid);
                    $this->sender->push(
                        $fd,
                        $packet,
                        SWOOLE_WEBSOCKET_OPCODE_TEXT,
                        $wsFlag
                    );
                    $pushed[$fd] = true;
                }
            }
        } else {
            foreach (array_keys($this->sids) as $sid) {
                $sid = strval($sid);
                if (in_array($sid, $except)) {
                    continue;
                }
                $fd = $this->sidProvider->getFd($sid);
                $this->sender->push($fd, $packet, SWOOLE_WEBSOCKET_OPCODE_TEXT, $wsFlag);
            }
        }
    }

    public function clients(string ...$rooms): array
    {
        $pushed = [];
        $result = [];
        if (! empty($rooms)) {
            foreach ($rooms as $room) {
                if (! isset($this->rooms[$room])) {
                    continue;
                }
                $room = $this->rooms[$room];
                foreach ($room->list() as $sid) {
                    $sid = strval($sid);
                    if (isset($pushed[$sid])) {
                        continue;
                    }
                    $result[] = $sid;
                    $pushed[$sid] = true;
                }
            }
        } else {
            foreach (array_keys($this->sids) as $sid) {
                $result[] = strval($sid);
            }
        }
        return $result;
    }

    public function clientRooms(string $sid): array
    {
        return array_map('strval', array_keys($this->sids[$sid] ?? []));
    }
}
