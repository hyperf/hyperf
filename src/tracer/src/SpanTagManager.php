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
namespace Hyperf\Tracer;

class SpanTagManager
{
    // TODO: The properties will be changed to standard version in v1.2.
    private $tags = [
        'http_client' => [
            'http.status_code' => 'status',
        ],
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.sql',
            'db.query_time' => 'db.query_time',
        ],
    ];

    public function apply(array $tags): void
    {
        $this->tags = array_replace_recursive($this->tags, $tags);
    }

    public function get(string $type, string $name): string
    {
        return $this->tags[$type][$name];
    }

    public function has(string $type, string $name): bool
    {
        return isset($this->tags[$type][$name]);
    }
}
