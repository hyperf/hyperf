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
use Hyperf\View\Mode;
use Hyperf\ViewEngine\HyperfViewEngine;

return [
    'engine' => HyperfViewEngine::class,
    'mode' => Mode::SYNC,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
        'charset' => 'UTF-8',
    ],

    // Autoload components.
    'autoload' => [
        'classes' => [
            'App\View\Component\\',
        ],
        'components' => [
            'components.', // BASE_PATH . '/storage/view/components/'
        ],
    ],

    # Custom components.
    'components' => [
        // 'other-alert' => \Other\ViewComponent\Alert::class
    ],

    # View namespaces. (Used for packages)
    'namespaces' => [
        // 'admin' => BASE_PATH . '/storage/view/vendor/admin',
    ],
];
