# http_consul_register

## 功能
目前hyperf 只实现了微服务之间的rpc 协议的server 注册到consul

这个包基于hyperf将hyperf 监听的http server 注册到consul，使得 consul 的内置dns 可以通过注册的service name 找到提供http1.1协议的可用服务实例


### 安装 
```
composer require hyperf/http_consul_register

```

### 配置
```
// config/autoload/consul.php

return [
    'uri' => '127.0.0.1:8500',
    'http_consul_register' => false, // 是否开启注册http server 到consul
    'service_name' => 'front-api-gateway' // 注册到consul 的服务名称
];

```
！注意config/autoload/server.php 必须有 name 为 http 的server，且只注册name为http 的server
