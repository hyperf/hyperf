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
namespace Hyperf\SocketIOServer\Room;

interface AdapterInterface
{
    /**
     * add adds a known sid to one or more room
     */
    public function add(string $sid, string ...$rooms);

    /**
     * del removes a sid from multiple rooms. If none of the room is
     * given, the sid will be removed from all rooms.
     */
    public function del(string $sid, string ...$rooms);

    /**
     * broadcast sends a packet out based the options specified in $opts.
     */
    public function broadcast($packet, $opts);

    /**
     * clients method lists all sids in the given rooms, using junction.
     */
    public function clients(string ...$rooms): array;

    /**
     * clientRooms method lists all rooms a given sid has joined.
     */
    public function clientRooms(string $sid): array;
}
