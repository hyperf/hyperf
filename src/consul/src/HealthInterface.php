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

interface HealthInterface
{
    public function node($node, array $options = []);

    public function checks($service, array $options = []);

    public function service($service, array $options = []);

    public function state($state, array $options = []);
}
