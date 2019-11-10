<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Encrypt\Handler;

interface EncryptHandlerInterface
{
    public function encrypt($data): string;

    public function decrypt(string $encryptData);

    public function sign($data);

    public function verify(string $raw);
}
