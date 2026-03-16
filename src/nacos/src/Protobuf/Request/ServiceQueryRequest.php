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

class ServiceQueryRequest implements RequestInterface
{
    public function __construct(public NamingRequest $request, public string $cluster, public bool $healthyOnly, public int $udpPort)
    {
    }

    public function getValue(): array
    {
        return array_merge($this->request->toArray(), [
            'cluster' => $this->cluster,
            'healthyOnly' => $this->healthyOnly,
            'udpPort' => $this->udpPort,
        ]);
    }

    public function getType(): string
    {
        return 'ServiceQueryRequest';
    }
}
