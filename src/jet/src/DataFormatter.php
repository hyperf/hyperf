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
namespace Hyperf\Jet;

use Hyperf\Rpc\Contract\DataFormatterInterface;

class DataFormatter implements DataFormatterInterface
{
    public function formatRequest($data)
    {
        [$path, $params, $id] = $data;
        return [
            'jsonrpc' => '2.0',
            'method' => $path,
            'params' => $params,
            'id' => $id,
            'data' => [],
        ];
    }

    public function formatResponse($data)
    {

    }

    public function formatErrorResponse($data)
    {

    }
}
