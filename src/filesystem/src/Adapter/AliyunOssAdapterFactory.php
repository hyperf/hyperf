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
use Hyperf\Filesystem\Version;
use Hyperf\Flysystem\OSS\Adapter;
use Xxtime\Flysystem\Aliyun\OssAdapter;

class AliyunOssAdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        if (Version::isV2()) {
            return new Adapter($options);
        }
        return new OssAdapter($options);
    }
}
