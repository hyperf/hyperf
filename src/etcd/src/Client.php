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

namespace Hyperf\Etcd;

use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;
use Hyperf\Utils\Coroutine;

abstract class Client
{
    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var array
     */
    protected $options = [];

    public function __construct(string $baseUri, array $options)
    {
        $this->baseUri = $baseUri;
        $this->options = $options;
    }

    protected function getDefaultHandler()
    {
        $handler = null;
        if (Coroutine::inCoroutine()) {
            $handler = make(PoolHandler::class, [
                'option' => [
                    'max_connections' => 50,
                ],
            ]);
        }

        // Retry Middleware
        $retry = make(RetryMiddleware::class, [
            'retries' => 1,
            'delay' => 10,
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($retry->getMiddleware(), 'retry');

        return $stack;
    }
}
