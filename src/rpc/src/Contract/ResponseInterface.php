<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Rpc\Contract;

use Swoole\Server as SwooleServer;

interface ResponseInterface
{
    public function send(): bool;

    public function getServer(): SwooleServer;

    public function setServer(SwooleServer $server);

    public function getError(): array;

    public function setError(int $code, string $message, $data = null);

    public function getRequestId(): string;

    public function setRequestId(string $requestId);
}
