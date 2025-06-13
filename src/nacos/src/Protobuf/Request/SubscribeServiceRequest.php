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

class SubscribeServiceRequest implements RequestInterface
{
    public function __construct(public NamingRequest $request, public bool $subscribe = true, public string $clusters = 'DEFAULT')
    {
    }

    public function getValue(): array
    {
        return array_merge($this->request->toArray(), [
            'clusters' => $this->clusters,
            'subscribe' => $this->subscribe,
        ]);
    }

    public function getType(): string
    {
        return 'SubscribeServiceRequest';
    }
}
