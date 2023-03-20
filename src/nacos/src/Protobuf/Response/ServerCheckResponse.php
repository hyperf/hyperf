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

class ServerCheckResponse extends Response
{
    public string $connectionId;

    public function __construct(array $json)
    {
        $this->connectionId = $json['connectionId'];

        parent::__construct(...parent::namedParameters($json));
    }
}
