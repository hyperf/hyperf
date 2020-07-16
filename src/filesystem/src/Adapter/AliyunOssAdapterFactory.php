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
namespace Hyperf\Filesystem\Adapter;

use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use League\Flysystem\AdapterInterface;
use Xxtime\Flysystem\Aliyun\OssAdapter;

class AliyunOssAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @throws \Exception
     */
    public function make(array $options): AdapterInterface
    {
        return new OssAdapter($options);
    }
}
