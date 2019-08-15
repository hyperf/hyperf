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

namespace Hyperf\Translation;

use Hyperf\Translation\Contracts\Loader;
use Hyperf\Translation\Contracts\Translator;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Loader::class => FileLoaderFactory::class,
                Translator::class => TranslatorFactory::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for translation.',
                    'source' => __DIR__ . '/../publish/translation.php',
                    'destination' => BASE_PATH . '/config/autoload/translation.php',
                ],
            ],
        ];
    }
}
