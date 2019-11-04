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

namespace Hyperf\Tracer;

class SpanTagManager
{
    private $tag = [
        'http_client' => [
            'http.status_code' => 'status',
        ],
        'redis'       => [
            'arguments' => 'arguments',
            'result'    => 'result',
        ],
        'db'          => [
            'db.query'      => 'db.query',
            'db.statement'  => 'db.sql',
            'db.query_time' => 'db.query_time'
        ]
    ];

    public function apply(array $tags): void
    {
        $this->tag = array_replace_recursive($this->tag, $tags);
    }

    public function get(string $type, string $name): string
    {
        return $this->tag[$type][$name] ?? $name;
    }

    public function has(string $type, string $name): bool
    {
        return isset($this->tag[$type][$name]);
    }
}
