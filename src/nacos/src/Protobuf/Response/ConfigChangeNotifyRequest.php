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

use Hyperf\Codec\Json;
use JsonSerializable;

class ConfigChangeNotifyRequest extends Response implements JsonSerializable
{
    public string $tenant;

    public string $group;

    public string $dataId;

    private array $json;

    public function __construct(array $json)
    {
        $this->requestId = $json['requestId'];
        $this->tenant = $json['tenant'];
        $this->group = $json['group'];
        $this->dataId = $json['dataId'];
        $this->json = $json;
    }

    public function __toString(): string
    {
        return Json::encode($this->json);
    }

    public function jsonSerialize(): mixed
    {
        return $this->json;
    }
}
