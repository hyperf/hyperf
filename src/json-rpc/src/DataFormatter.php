<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\JsonRpc;

use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Utils\Packer\JsonPacker;

class DataFormatter implements DataFormatterInterface
{

    /**
     * @var JsonPacker
     */
    private $packer;

    public function __construct()
    {
        $this->packer = make(JsonPacker::class);
    }

    public function format($data)
    {
        [$serviceName, $path, $params] = $data;
        return [
            'jsonrpc' => '2.0',
            'method' => $path,
            'params' => $params,
        ];
    }
}
