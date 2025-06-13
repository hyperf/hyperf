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

namespace Hyperf\Nacos\Protobuf\Request;

class ConnectionSetupRequest extends Request
{
    public function __construct(public string $tenant, public string $module = 'config')
    {
    }

    public function getValue(): array
    {
        return [
            'tenant' => $this->tenant,
            'clientVersion' => 'Nacos-Hyperf-Client:v3.1',
            'labels' => [
                'source' => 'sdk',
                'AppName' => '',
                'taskId' => '0',
                'module' => $this->module,
            ],
            'module' => 'internal',
        ];
    }

    public function getType(): string
    {
        return 'ConnectionSetupRequest';
    }
}
