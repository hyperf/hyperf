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

use Hyperf\Nacos\Protobuf\ListenContext;

class ConfigChangeBatchListenResponse extends Response
{
    /**
     * @var ListenContext[]
     */
    public array $changedConfigs = [];

    public function __construct(array $json)
    {
        foreach ($json['changedConfigs'] ?? [] as $value) {
            $this->changedConfigs[] = ListenContext::jsonDeSerialize($value);
        }

        parent::__construct(...parent::namedParameters($json));
    }
}
