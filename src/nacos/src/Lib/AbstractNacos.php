<?php

namespace Hyperf\Nacos\Lib;

use Hyperf\Nacos\Util\Guzzle;

abstract class AbstractNacos
{
    protected $baseInfo = [];

    public function __construct()
    {
        $this->baseInfo = config('nacos');
    }

    public function request($method, $api, $params = [], $headers = [])
    {
        return Guzzle::request($method, $this->getServerUri() . $api, $params, $headers);
    }

    public function getServerUri()
    {
        return $this->baseInfo['host'] . ':' . $this->baseInfo['port'];
    }
}
