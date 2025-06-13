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

namespace Hyperf\Nacos\Protobuf\Response;

use Hyperf\Nacos\Protobuf\ServiceInfo;
use JsonSerializable;

class NotifySubscriberRequest extends Response implements JsonSerializable
{
    public string $module;

    public ServiceInfo $serviceInfo;

    public function __construct(private array $json)
    {
        $this->requestId = $json['requestId'];
        $this->module = $json['module'];
        $this->serviceInfo = is_array($json['serviceInfo']) ? ServiceInfo::jsonDeSerialize($json['serviceInfo']) : $json['serviceInfo'];
    }

    public function jsonSerialize(): mixed
    {
        return $this->json;
    }
}
