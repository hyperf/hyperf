<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * Date: 2019/9/10
 * Time: 16:42
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session\Handler;


interface HandlerInterface
{
    public function open(string $sessionPath, string $sessionName): bool;

    public function read(string $sessionId): array;

    public function destroy(string $sessionId): bool;

    public function write(string $sessionId, array $sessionData, int $maxLifetime): bool;
}