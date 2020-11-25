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

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\ServiceGovernance\Grpc\Health\HealthCheckResponse;
use Hyperf\ServiceGovernance\Grpc\Health\HealthCheckRequest;

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
        $message = new HealthCheckResponse();
        $message->setStatus(HealthCheckResponse\ServingStatus::SERVING);
        return $message;
    }

    /**
     * @PostMapping(path="Watch")
     */
    public function watch(HealthCheckRequest $request)
    {
        $message = new HealthCheckResponse();
        $message->setStatus(HealthCheckResponse\ServingStatus::SERVING);
        return $message;
    }
}
