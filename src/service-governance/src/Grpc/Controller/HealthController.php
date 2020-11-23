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
namespace Hyperf\ServiceGovernance\Grpc\Controller;

use Grpc\Health\V1\HealthCheckRequest;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * @Controller(prefix="/grpc.health.v1.Health", server="grpc")
 */
class HealthController
{
    /**
     * @PostMapping(path="Check")
     */
    public function check(HealthCheckRequest $request)
    {
        $message = new \Grpc\Health\V1\HealthCheckResponse();
        $message->setStatus(\Grpc\Health\V1\HealthCheckResponse\ServingStatus::SERVING);
        return $message;
    }

    /**
     * @PostMapping(path="Watch")
     */
    public function watch(HealthCheckRequest $request)
    {
        $message = new \Grpc\Health\V1\HealthCheckResponse();
        $message->setStatus(\Grpc\Health\V1\HealthCheckResponse\ServingStatus::SERVING);
        return $message;
    }
}
