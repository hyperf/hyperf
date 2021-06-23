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
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;

Coroutine::set([
    'hook_flags' => SWOOLE_HOOK_ALL,
]);

Coroutine\run(function () {
    $server = new Server('0.0.0.0', 10001);
    $server->handle(static function (Connection $conn) {
        try {
            while (true) {
                $res = $conn->recv();
                if ($res === '') {
                    break;
                }
                $data = json_decode($res, true);
                switch ($data['id'] ?? null) {
                    case 'timeout':
                        sleep($data['timeout']);
                        $conn->send($data);
                        break;
                    default:
                        $conn->send('ack: ' . $data['ack']);
                        break;
                }
            }
        } catch (\Throwable $exception) {
            var_dump((string) $exception);
        }
    });
    $server->start();
});
