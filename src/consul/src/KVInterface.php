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

namespace Hyperf\Consul;

interface KVInterface
{
    public function get($key, array $options = []): ConsulResponse;

    public function put($key, $value, array $options = []): ConsulResponse;

    public function delete($key, array $options = []): ConsulResponse;
}
