<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Consul;

interface SessionInterface
{
    public function create($body = null, array $options = []);

    public function destroy($sessionId, array $options = []);

    public function info($sessionId, array $options = []);

    public function node($node, array $options = []);

    public function all(array $options = []);

    public function renew($sessionId, array $options = []);
}
