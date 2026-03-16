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

use Hyperf\Contract\Arrayable;

class NamingRequest implements Arrayable
{
    public function __construct(public string $serviceName, public string $groupName, public string $namespace)
    {
    }

    public function toArray(): array
    {
        return [
            'namespace' => $this->namespace,
            'serviceName' => $this->serviceName,
            'groupName' => $this->groupName,
            'module' => 'naming',
        ];
    }
}
