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
namespace Hyperf\Encryption;

use Hyperf\Contract\EncrypterInterface;
use Hyperf\Encryption\Commands\EncrypterCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EncrypterInterface::class => EncrypterFactory::class,
            ],
            'commands' => [
                EncrypterCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'encryption',
                    'description' => 'The config for encryption.',
                    'source' => __DIR__ . '/../publish/encryption.php',
                    'destination' => BASE_PATH . '/config/autoload/encryption.php',
                ],
            ],
        ];
    }
}
