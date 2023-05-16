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

class HealthCheckRequest extends Request
{
    public function getValue(): array
    {
        return [
            'module' => 'internal',
        ];
    }

    public function getType(): string
    {
        return 'HealthCheckRequest';
    }
}
