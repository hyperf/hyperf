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
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Memory\MemoryAdapter;

class MemoryAdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        if (Version::isV2()) {
            return new InMemoryFilesystemAdapter();
        }
        return new MemoryAdapter();
    }
}
