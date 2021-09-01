<?php

declare(strict_types=1);

return [
    // enable false 将不会启动服务
    'enable' => true,
    'admin_address' => 'http://127.0.0.1:8769/xxl-job-admin',
    'app_name' => 'xxl-job-demo',
    'prefix_url' => 'php-xxl-job',
    //access_token
    'access_token' => null,
    'log' => [
        'filename' => BASE_PATH . '/runtime/logs/xxl-job/job.log',
        'maxDay' => 30,
    ],
];
