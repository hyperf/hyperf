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

namespace Hyperf\JsonRpc;

use Hyperf\Contract\ConfigInterface;

abstract class AbstractJsonRpcTransporter
{
    /**
     * @var array
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('json_rpc.transporter.tcp', []);
    }
}
