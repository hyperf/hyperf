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

use Hyperf\Nacos\Protobuf\Message\Service;

class SubscribeServiceResponse extends Response
{
    public Service $service;

    public function __construct(array $json)
    {
        $this->service = Service::jsonDeSerialize($json['serviceInfo']);

        parent::__construct(...parent::namedParameters($json));
    }
}
