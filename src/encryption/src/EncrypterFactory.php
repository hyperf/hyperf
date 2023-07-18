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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class EncrypterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);

        $key = $config->get('encryption.key');
        $cipher = $config->get('encryption.cipher');

        if (empty($key)) {
            throw new InvalidArgumentException('The encryption key is invalid.');
        }

        return new Encrypter($this->parseKey($key), $cipher);
    }

    private function parseKey($key)
    {
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr_replace($key, '', 0, 7));
        }

        return $key;
    }
}
