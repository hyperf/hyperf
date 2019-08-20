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

namespace Hyperf\Validation;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Hyperf\Validation\Contracts\Validation\Validator::class => \Hyperf\Validation\ValidatorFactory::class,
                \Hyperf\Validation\PresenceVerifierInterface::class => \Hyperf\Validation\DatabasePresenceVerifierFactory::class,
                \Hyperf\Validation\Contracts\Validation\Factory::class => \Hyperf\Validation\ValidatorFactory::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            'publish' => [
                [
                    'id' => 'zh_CN',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/zh_CN/validation.php',
                    'destination' => BASE_PATH . '/resources/lang/zh_CN/validation.php',
                ],
                [
                    'id' => 'en',
                    'description' => 'The message bag for validation.',
                    'source' => __DIR__ . '/../publish/en/validation.php',
                    'destination' => BASE_PATH . '/resources/lang/en/validation.php',
                ],
            ],
        ];
    }
}
