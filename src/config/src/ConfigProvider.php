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

namespace Hyperf\Config;

use Hyperf\Config\Annotation\ValueAspect;
use Hyperf\Config\Listener\RegisterPropertyHandlerListener;
use Hyperf\Contract\ConfigInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConfigInterface::class => ConfigFactory::class,
            ],
            'aspects' => [
                ValueAspect::class,
            ],
            'listeners' => [
                RegisterPropertyHandlerListener::class,
            ],
        ];
    }
}
