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
use Hyperf\Contract\ConfigInterface;
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
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $options = [];

    protected $client;

    public function __construct(ConfigInterface $config)
    {
        $uri = $config->get('etcd.uri', 'http://127.0.0.1:2379');
        $version = $config->get('etcd.version', 'v3beta');

        $this->options = $config->get('etcd.options', []);
        $this->baseUri = sprintf('%s/%s/', $uri, $version);
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
