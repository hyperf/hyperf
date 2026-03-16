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

namespace Hyperf\SuperGlobals;

use Hyperf\SuperGlobals\Listener\SuperGlobalsInitializeListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                SuperGlobalsInitializeListener::class,
            ],
        ];
    }
}
