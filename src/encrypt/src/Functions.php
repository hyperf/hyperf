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

use Hyperf\Encrypt\Handler\EncryptHandlerInterface;
use Hyperf\Encrypt\SecretKeyInterface;

if (! function_exists('makeEncryptHandler')) {
    function makeEncryptHandler()
    {
        return make(EncryptHandlerInterface::class, ['secretKey' => make(SecretKeyInterface::class, config('encrypt', []))]);
    }
}

if (! function_exists('encrypt')) {
    function encrypt($data)
    {
        return makeEncryptHandler()->encrypt($data);
    }
}

if (! function_exists('decrypt')) {
    function decrypt(string $data)
    {
        return makeEncryptHandler()->decrypt($data);
    }
}

if (! function_exists('sign')) {
    function sign($data)
    {
        return makeEncryptHandler()->sign($data);
    }
}

if (! function_exists('verify')) {
    function verify(string $data)
    {
        return makeEncryptHandler()->verify($data);
    }
}
