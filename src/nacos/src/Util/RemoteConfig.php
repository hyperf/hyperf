<?php

namespace Hyperf\Nacos\Util;

use Hyperf\Nacos\Lib\NacosConfig;
use Hyperf\Nacos\Model\ConfigModel;

class RemoteConfig
{
    public static function get()
    {
        $listener = config('nacos.listenerConfig');
        /** @var NacosConfig $nacos_config */
        $config = [];
        foreach ($listener as $item) {
            $each = (new ConfigModel($item))->getValue() ?? [];
            $config = array_merge_recursive($config, $each);
        }

        return $config;
    }
}
