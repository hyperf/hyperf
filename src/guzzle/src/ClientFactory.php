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

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class ClientFactory
{
    public static function createClient(array $options = []): Client
    {
        $stack = HandlerStack::create(new CoroutineHandler());
        return new Client(array_replace(['handler' => $stack], $options));
    }
}
