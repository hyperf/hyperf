<?php


namespace Hyperf\Consul;


interface HealthInterface
{

    public function node($node, array $options = array());

    public function checks($service, array $options = array());

    public function service($service, array $options = array());

    public function state($state, array $options = array());

}