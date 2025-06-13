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

class ConfigQueryRequest extends Request
{
    public function __construct(public string $tenant, public string $group, public string $dataId)
    {
    }

    public function getValue(): array
    {
        return [
            'tenant' => $this->tenant,
            'group' => $this->group,
            'dataId' => $this->dataId,
            'module' => 'config',
        ];
    }

    public function getType(): string
    {
        return 'ConfigQueryRequest';
    }
}
