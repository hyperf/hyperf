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
     *
     * @param array $array with one of the following formats:
     *     [Dispatcher::NOT_FOUND]
     *     [Dispatcher::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [Dispatcher::FOUND, $handler, ['varName' => 'value', ...]]
     */
    public function __construct(array|Matched|MethodNotAllowed|NotMatched $result, public ?string $serverName = null)
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

        if ($result instanceof NotMatched) {
            $this->status = Dispatcher::NOT_FOUND;
            return;
        }

        $this->status = $result[0];
        switch ($this->status) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->params = $result[1];
                break;
            case Dispatcher::FOUND:
                $this->handler = $result[1];
                $this->params = $result[2];
                break;
        }
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
