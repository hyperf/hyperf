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
use Hyperf\Codec\Json;
use Hyperf\Engine\WebSocket\WebSocket;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

use function Swoole\Coroutine\run;

require_once 'vendor/autoload.php';

Coroutine::set([
    'hook_flags' => SWOOLE_HOOK_ALL,
]);

$callback = function () {
    $server = new Server('0.0.0.0', 10002);
    $server->handle('/', function (Request $request, Response $response) {
        $response->setHeader('Server', 'Hyperf');
        switch ($request->server['request_uri']) {
            case '/':
                $response->end($request->rawContent());
                break;
            case '/ws':
                $upgrade = new WebSocket($response, $request);
                $upgrade->on(WebSocket::ON_MESSAGE, function (Response $response, Frame $frame) use ($request) {
                    match ($frame->data) {
                        'ping' => $response->push('pong'),
                        'headers' => $response->push(Json::encode($request->header))
                    };
                });
                $upgrade->start();
                $response->end();
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
