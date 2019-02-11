<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Tracer\Tracing;
use Hyperf\Utils\Arr;

/**
 * @Listener
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var Tracing
     */
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    $sql = str_replace('?', '"%s"', $sql);
                }

                $sql = sprintf($sql, ...$event->bindings);
            }
            $endTime = microtime(true);
            $span = $this->tracing->span('db.query');
            $span->start((int) (($endTime - $event->time / 1000) * 1000 * 1000));
            $span->tag('db.sql', $sql);
            $span->tag('db.query_time', $event->time . ' ms');
            $span->finish((int) ($endTime * 1000 * 1000));
        }
    }
}
