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
namespace Hyperf\ServiceGovernance\Grpc;

use Hyperf\HttpServer\Router\Router;

class Grpc
{
    /**
     * An easy way to add health check to Grpc services.
     *
     * @param string $server
     */
    public static function addHealthCheck(string $server)
    {
        Router::addServer($server, function () {
            Router::addGroup('/grpc.health.v1.Health', function () {
                Router::post('/Check', 'Hyperf\ServiceGovernance\Grpc\HealthController@check');
                Router::post('/Watch', 'Hyperf\ServiceGovernance\Grpc\HealthController@watch');
            });
        });
    }
}
