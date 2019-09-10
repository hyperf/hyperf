<?php
declare(strict_types = 1);
namespace Hyperf\Apidog;

use Hyperf\Apidog\Validation\ValidationInterface;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ValidationInterface::class => \Hyperf\Apidog\Validation\Validation::class
            ],
            'commands' => [],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
