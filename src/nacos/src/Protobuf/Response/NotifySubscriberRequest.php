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

use Hyperf\Nacos\Protobuf\Response\Response;
use JsonSerializable;

class NotifySubscriberRequest extends Response implements JsonSerializable
{
    public function __construct(array $json)
    {
        var_dump($json);
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
