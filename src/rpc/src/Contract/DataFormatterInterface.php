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
namespace Hyperf\Rpc\Contract;

interface DataFormatterInterface
{
    /**
     * @param array $data [$path, $params, $id]
     */
    public function formatRequest(array $data): array;

    /**
     * @param array $data [$id, $result]
     */
    public function formatResponse(array $data): array;

    /**
     * @param array $data [$id, $code, $message, $exception]
     */
    public function formatErrorResponse(array $data): array;
}
