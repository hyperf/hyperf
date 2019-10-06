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

return [
    'enable' => false,
    'interval' => 5,
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    'access_key' => env('ALIYUN_ACM_AK', ''),
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
