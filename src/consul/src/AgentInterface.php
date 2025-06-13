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

interface AgentInterface
{
    public function checks(): ConsulResponse;

    public function services(): ConsulResponse;

    public function members(array $options = []): ConsulResponse;

    public function self(): ConsulResponse;

    public function join($address, array $options = []): ConsulResponse;

    public function forceLeave($node): ConsulResponse;

    public function registerCheck($check): ConsulResponse;

    public function deregisterCheck($checkId): ConsulResponse;

    public function passCheck($checkId, array $options = []): ConsulResponse;

    public function warnCheck($checkId, array $options = []): ConsulResponse;

    public function failCheck($checkId, array $options = []): ConsulResponse;

    public function registerService(array $service): ConsulResponse;

    public function deregisterService($serviceId): ConsulResponse;
}
