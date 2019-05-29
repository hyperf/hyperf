<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'enable' => true,
    'interval' => 5,
    'addressServer' => env('ALIYUN_ACM_ADDRESS_SERVER', 'acm.aliyun.com'),
    'namespace' => env('ALIYUN_ACM_NAMESPACE', 'namespace-id'),
    'dataId' => env('ALIYUN_ACM_DATA_ID', 'app'),
    'group' => env('ALIYUN_ACM_GROUP', 'app'),
    'ak' => env('ALIYUN_ACM_AK', 'ak'),
    'sk' => env('ALIYUN_ACM_SK', 'sk'),
];
