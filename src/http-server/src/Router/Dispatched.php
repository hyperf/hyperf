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

class Dispatched
{
    public int $status;

    public ?Handler $handler = null;

    public array $params = [];

    /**
     * Dispatches against the provided HTTP method verb and URI.
     *
     * @param array $array with one of the following formats:
     *
     *     [Dispatcher::NOT_FOUND]
     *     [Dispatcher::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
     *     [Dispatcher::FOUND, $handler, ['varName' => 'value', ...]]
     */
    public function __construct(array $array, public ?string $serverName = null)
    {
        $this->status = $array[0];
        switch ($this->status) {
            case Dispatcher::METHOD_NOT_ALLOWED:
                $this->params = $array[1];
                break;
            case Dispatcher::FOUND:
                $this->handler = $array[1];
                $this->params = $array[2];
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
