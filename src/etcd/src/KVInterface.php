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

namespace Hyperf\Etcd;

interface KVInterface
{
    public function put($key, $value, array $options = []);

    public function get($key, array $options = []);

    public function fetchByPrefix($prefix);

    public function delete($key, array $options = []);
}
