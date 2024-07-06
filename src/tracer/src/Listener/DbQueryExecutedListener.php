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

namespace Hyperf\Tracer\Listener;

use Hyperf\Collection\Arr;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Stringable\Str;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;

class DbQueryExecutedListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
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
    public function process(object $event): void
    {
        if ($this->switchManager->isEnable('db') === false) {
            return;
        }
        $sql = $event->sql;
        if (! Arr::isAssoc($event->bindings)) {
            foreach ($event->bindings as $value) {
                $sql = Str::replaceFirst('?', "'{$value}'", $sql);
            }
        }

        $endTime = microtime(true);
        $span = $this->startSpan($this->spanTagManager->get('db', 'db.query'), [
            'start_time' => (int) (($endTime - $event->time / 1000) * 1000 * 1000),
        ]);
        $span->setTag($this->spanTagManager->get('db', 'db.statement'), $sql);
        $span->setTag($this->spanTagManager->get('db', 'db.query_time'), $event->time . ' ms');
        $span->finish((int) ($endTime * 1000 * 1000));
    }
}
