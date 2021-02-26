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
namespace Hyperf\Validation;

use Hyperf\Validation\Contract\PresenceVerifierInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface as FactoryInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        $languagesPath = BASE_PATH . '/storage/languages';
        $translationConfigFile = BASE_PATH . '/config/autoload/translation.php';
        if (file_exists($translationConfigFile)) {
            $translationConfig = include $translationConfigFile;
            $languagesPath = $translationConfig['path'] ?? $languagesPath;
        }

        return [
            'dependencies' => [
                PresenceVerifierInterface::class => DatabasePresenceVerifierFactory::class,
                FactoryInterface::class => ValidatorFactoryFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'zh_CN',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/zh_CN/validation.php',
                    'destination' => $languagesPath . '/zh_CN/validation.php',
                ],
                [
                    'id' => 'en',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/en/validation.php',
                    'destination' => $languagesPath . '/en/validation.php',
                ],
            ],
        ];
    }
}
