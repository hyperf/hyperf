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
        ];
    }
}
