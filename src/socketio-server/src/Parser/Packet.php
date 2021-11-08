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
namespace Hyperf\SocketIOServer\Parser;

class Packet implements \ArrayAccess
{
    public const OPEN = '0';

    public const CLOSE = '1';

    public const EVENT = '2';

    public const ACK = '3';

    public string $id;

    public string $type;

    public string $nsp;

    public ?array $data;

    public mixed $query;

    private function __construct()
    {
    }

    public static function create(array $decoded)
    {
        $new = new Packet();
        $new->id = (string) ($decoded['id'] ?? '');
        $new->type = (string) $decoded['type'];
        if (isset($decoded['nsp'])) {
            $new->nsp = (string) ($decoded['nsp'] ?: '/');
        } else {
            $new->nsp = '/';
        }
        $data = $decoded['data'] ?? null;
        $new->data = is_array($data) ? $data : null;
        $new->query = $decoded['query'] ?? null;
        return $new;
    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}
