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

class NotifySubscriberResponse extends Request
{
    public function __construct(public string $requestId, public int $resultCode = 200)
    {
    }

    public function getValue(): array
    {
        return [
            'resultCode' => $this->resultCode,
            'requestId' => $this->requestId,
        ];
    }

    public function getType(): string
    {
        return 'NotifySubscriberResponse';
    }
}
