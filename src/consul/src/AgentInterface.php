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

interface AgentInterface
{
    public function checks();

    public function services();

    public function members(array $options = []);

    public function self();

    public function join($address, array $options = []);

    public function forceLeave($node);

    public function registerCheck($check);

    public function deregisterCheck($checkId);

    public function passCheck($checkId, array $options = []);

    public function warnCheck($checkId, array $options = []);

    public function failCheck($checkId, array $options = []);

    public function registerService($service);

    public function deregisterService($serviceId);
}
