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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Encryption\Contract\EncrypterInterface;
use Psr\Container\ContainerInterface;

class EncrypterFactory
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    public function get(string $name): EncrypterInterface
    {
        $encryption = $this->config->get('encryption.' . $name, []);
        $key = $encryption['key'] ?? '';
        $cipher = $encryption['cipher'] ?? 'AES-128-CBC';

        return new Encrypter($key, $cipher);
    }
}
