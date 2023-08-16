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

use Hyperf\Nacos\Protobuf\ListenContext;

class ConfigBatchListenRequest extends Request
{
    /**
     * @param ListenContext[] $configListenContexts
     */
    public function __construct(public bool $listen, public array $configListenContexts)
    {
    }

    public function getValue(): array
    {
        return [
            'listen' => $this->listen,
            'module' => 'config',
            'configListenContexts' => $this->configListenContexts,
        ];
    }

    public function getType(): string
    {
        return 'ConfigBatchListenRequest';
    }
}
