<?php

namespace Hyperf\Nacos;

use Hyperf\Nacos\Model\ServiceModel;

class ThisService extends ServiceModel
{
    public function __construct()
    {
        $config = config('nacos.service');
        parent::__construct($config);
    }
}
