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
namespace Hyperf\Encryption\Contract;

use RuntimeException;

interface EncrypterInterface
{
    /**
     * Encrypt the given value.
     *
     * @param mixed $value
     * @throws RuntimeException
     */
    public function encrypt($value, bool $serialize = true): string;

    /**
     * Decrypt the given value.
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function decrypt(string $payload, bool $unserialize = true);
}
