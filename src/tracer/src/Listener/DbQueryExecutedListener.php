<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Listener;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\Tracing;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;

/**
 * @Listener
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var Tracing
     */
    private $tracing;

    /**
     * @var SwitchManager
     */
    private $switchManager;

    public function __construct(Tracing $tracing, SwitchManager $switchManager)
    {
        $this->tracing = $tracing;
        $this->switchManager = $switchManager;
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
        if ($this->switchManager->isEnable('db') === false) {
            return;
        }
        $sql = $event->sql;
        if (! Arr::isAssoc($event->bindings)) {
            foreach ($event->bindings as $key => $value) {
                $sql = Str::replaceFirst('?', "'{$value}'", $sql);
            }
        }

        $endTime = microtime(true);
        $span = $this->tracing->span('db.query');
        $span->start((int) (($endTime - $event->time / 1000) * 1000 * 1000));
        $span->tag('db.sql', $sql);
        $span->tag('db.query_time', $event->time . ' ms');
        $span->finish((int) ($endTime * 1000 * 1000));
    }
}
