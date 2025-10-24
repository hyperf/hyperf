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

namespace Hyperf\HttpServer\Router;

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\Result\Matched;
use FastRoute\Dispatcher\Result\MethodNotAllowed;
use FastRoute\Dispatcher\Result\NotMatched;

class Dispatched
{
    public int $status;

    public ?Handler $handler = null;

    public array $params = [];

    /**
     * Dispatches against the provided HTTP method verb and URI.
     */
    public function __construct(Matched|MethodNotAllowed|NotMatched $result, public ?string $serverName = null)
    {
        if ($result instanceof Matched) {
            $this->status = Dispatcher::FOUND;
            $this->handler = $result->handler;
            $this->params = $result->variables;
            return;
        }

        if ($result instanceof MethodNotAllowed) {
            $this->status = Dispatcher::METHOD_NOT_ALLOWED;
            $this->params = $result->allowedMethods;
            return;
        }

        // At this point, $result must be NotMatched since we've already handled Matched and MethodNotAllowed cases
        $this->status = Dispatcher::NOT_FOUND;
    }

    public function isFound(): bool
    {
        return $this->status === Dispatcher::FOUND;
    }

    public function isNotFound(): bool
    {
        return $this->status === Dispatcher::NOT_FOUND;
    }
}
