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

namespace Hyperf\WebSocketClient;

use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Utils\Str;

class ClientFactory
{
    public function create(string $uri, bool $autoClose = true): Client
    {
        if (! Str::startsWith($uri, ['ws://', 'wss://'])) {
            $uri = 'ws://' . $uri;
        }
        $client = make(Client::class, ['uri' => new Uri($uri)]);
        if ($autoClose) {
            defer(function () use ($client) {
                $client->close();
            });
        }
        return $client;
    }
}
