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
     * @return array
     */
    public function formatRequest($data);

    /**
     * @param array $data [$id, $result]
     * @return array
     */
    public function formatResponse($data);

    /**
     * @param array $data [$id, $code, $message, $exception]
     * @return array
     */
    public function formatErrorResponse($data);
}
