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
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;

use function Swoole\Coroutine\run;

Coroutine::set([
    'hook_flags' => SWOOLE_HOOK_ALL,
]);

$callback = function () {
    $server = new Server('0.0.0.0', 10002);
    $server->handle('/', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
        $response->setHeader('Server', 'Hyperf');
        switch ($request->server['request_uri']) {
            case '/':
                $response->end($request->rawContent());
                break;
            default:
                $response->setStatusCode(404);
                $response->end();
                break;
        }
    });
    $server->start();
};

run($callback);
