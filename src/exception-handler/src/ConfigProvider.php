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

namespace Hyperf\Logger;

use Hyperf\ExceptionHandler\Formatter\DefaultFormatter;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                FormatterInterface::class => DefaultFormatter::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
