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

use Hyperf\Nacos\Protobuf\Message\Instance;

class InstanceRequest implements RequestInterface
{
    public const TYPE_REGISTER = 'registerInstance';

    public const TYPE_BATCH_REGISTER = 'batchRegisterInstance';

    public const TYPE_DEREGISTER = 'deregisterInstance';

    public function __construct(public NamingRequest $request, public Instance $instance, public string $type)
    {
    }

    public function getValue(): array
    {
        return array_merge($this->request->toArray(), [
            'type' => $this->type,
            'instance' => $this->instance,
        ]);
    }

    public function getType(): string
    {
        return 'InstanceRequest';
    }
}
