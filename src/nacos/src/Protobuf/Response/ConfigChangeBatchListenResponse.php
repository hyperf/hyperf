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

class ConfigChangeBatchListenResponse extends Response
{
    protected array $changedConfigs;

    public function __construct(array $json)
    {
        $this->changedConfigs = $json['changedConfigs'] ?? [];

        parent::__construct(...parent::namedParameters($json));
    }

    /**
     * @return array [[
     *               'group' => '',
     *               'dataId' => '',
     *               'tenant' => '',
     *               ]]
     */
    public function getChangedConfigs(): array
    {
        return $this->changedConfigs;
    }
}
