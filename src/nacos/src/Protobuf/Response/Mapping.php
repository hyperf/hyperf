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
namespace Hyperf\Nacos\Protobuf\Response;

class Mapping
{
    public static $mappings = [
        'ServerCheckResponse' => ServerCheckResponse::class,
        'HealthCheckResponse' => HealthCheckResponse::class,
        'ConfigChangeBatchListenResponse' => ConfigChangeBatchListenResponse::class,
        'ConfigQueryResponse' => ConfigQueryResponse::class,
        'ConfigChangeNotifyRequest' => ConfigChangeNotifyRequest::class,
    ];
}
