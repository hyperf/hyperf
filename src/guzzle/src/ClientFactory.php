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

namespace Hyperf\Guzzle;

use Swoole\Coroutine;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use function GuzzleHttp\choose_handler;

class ClientFactory
{
    public static function createClient(array $options = []): Client
    {
        if (Coroutine::getCid() > 0) {
            $stack = HandlerStack::create(new CoroutineHandler());
        } else {
            $stack = HandlerStack::create(choose_handler());
        }

        $config = array_replace(['handler' => $stack], $options);
        return make(Client::class, compact('config'));
    }
}
