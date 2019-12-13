# 協程元件庫

所有官方提供的元件庫均已進行協程化處理，可安全地在 Hyperf 內或其它協程框架內使用，基於 Hyperf 的開放性和可擴充套件性，社群可對此開發或適配各種各樣的元件，得益於此，Hyperf 將存在著無限的可能性。   
本頁將收錄各個適配了 Hyperf 的協程元件 和 已經經過驗證可安全地用於協程下的常用庫，以便您快速的從中選擇合適的元件完成您的需求。

## 如何提交我的元件？

如果您開發的協程元件適配了 Hyperf，那麼您可以直接對 [hyperf/hyperf](https://github.com/hyperf/hyperf) 專案的 `master` 分支發起您的 `Pull Request`，也就是更改當前頁`(./doc/zh/awesome-components.md)`。

## 如何適配 Hyperf ?

我們為您提供了一份 [Hyperf 元件開發指南](zh-tw/component-guide/intro.md)，以幫助您開發 Hyperf 元件或適配 Hyperf 框架。

# 元件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一個常用的高速路由

## 事件

- [hyperf/event](https://github.com/hyperf/event) Hyperf 官方提供的基於 PSR-14 的事件管理器

## 日誌

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf 官方提供的基於 PSR-3 的日誌管理器，一個基於 monolog 的抽象及封裝

## 命令

- [hyperf/command](https://github.com/hyperf/command) Hyperf 官方提供的基於 [symfony/console](https://github.com/symfony/console) 擴充套件並支援註解的命令管理元件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的獨立命令管理元件

## 資料庫

- [hyperf/database](https://github.com/hyperf/database) Hyperf 官方提供的基於 Eloquent 衍生的資料庫 ORM，可複用於其它框架
- [hyperf/model](https://github.com/hyperf/model) Hyperf 官方提供的基於 [hyperf/database](https://github.com/hyperf/database) 元件的自動模型快取元件 
- [yurunsoft/influxdb-orm](https://github.com/Yurunsoft/influxdb-orm) InfluxDB 時序資料庫的 ORM，常用操作一把梭，支援 php-fpm、Swoole 環境，一鍵輕鬆切換。

## 依賴注入容器

- [hyperf/di](https://github.com/hyperf/di) Hyperf 官方提供的支援註解及 AOP 的依賴注入容器
- [reasno/lazy-loader](https://github.com/Reasno/LazyLoader) 為 Hyperf DI 補充基於型別提示的懶載入注入。

## 服務

- [hyperf/http-server](https://github.com/hyperf/http-server) Hyperf 官方提供的 HTTP 服務端
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) Hyperf 官方提供的 GRPC 服務端
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) Hyperf 官方提供的 WebSocket 服務端
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Hyperf 官方提供的通用 RPC 抽象服務端

## 客戶端

- [hyperf/consul](https://github.com/hyperf/consul) Hyperf 官方提供的 Consul 協程客戶端
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Hyperf 官方提供的 Elasticsearch 協程客戶端
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Hyperf 官方提供的 GRPC 協程客戶端
- [hyperf/etcd](https://github.com/hyperf/etcd) Hyperf 官方提供的 ETCD 協程客戶端
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Hyperf 官方提供的通用 RPC 抽象協程客戶端
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Hyperf 官方提供的 Guzzle HTTP 協程客戶端
- [hyperf/redis](https://github.com/hyperf/redis) Hyperf 官方提供的 Redis 協程客戶端
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Hyperf 官方提供的 WebSocket 協程客戶端
- [hyperf/cache](https://github.com/hyperf/cache) Hyperf 官方提供的基於 PSR-16 的快取協程客戶端，支援註解的使用方式

## 訊息佇列

- [hyperf/amqp](https://github.com/hyperf/amqp) Hyperf 官方提供的 AMQP 協程元件
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Hyperf 官方提供的簡單的基於 Redis 的非同步佇列元件
- [hooklife/hyperf-aliyun-amqp](https://github.com/hooklife/hyperf-aliyun-amqp) 使 hyperf/amqp 元件支援阿里雲 AMQP

## 配置中心

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Hyperf 官方提供的 Apollo 配置中心接入元件
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Hyperf 官方提供的阿里雲 ACM 應用配置服務接入元件
- [hyperf/config-etcd](https://github.com/hyperf/config-etcd) Hyperf 官方提供的 ETCD 配置中心接入元件

## RPC

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Hyperf 官方提供的 JSON-RPC 協議元件

## 服務治理

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Hyperf 官方提供的基於令牌桶演算法的限流元件
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Hyperf 官方提供的負載均衡元件
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Hyperf 官方提供的服務治理元件
- [hyperf/tracer](https://github.com/hyperf/tracer) Hyperf 官方提供的 OpenTracing 分散式呼叫鏈追蹤元件
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Hyperf 官方提供的服務熔斷元件

## 定時任務

- [hyperf/crontab](https://github.com/hyperf/crontab) Hyperf 官方提供的秒級定時任務元件

## ID 生成器

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Hyperf 官方提供的 Snowflake ID 生成器元件 (beta)

## 文件生成

- [hyperf/swagger](https://github.com/hyperf/swagger) Hyperf 官方提供的 Swagger 文件自動生成元件 (beta)

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf 官方提供的 Graphql 服務端元件 (beta)

## 熱更新/熱過載

- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一個基於 fswatch 實現的通用熱更新元件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一個由 Mixphp 實現的通用熱更新元件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一個基於 Go 語言實現的通用熱更新元件
- [remy/nodemon](https://github.com/remy/nodemon) 一個基於 node.js 實現的通用熱更新元件

> Warning: 請勿於生產環境使用 `熱更新/熱過載` 功能

## Swoole

- [hyperf/swoole-tracker](https://github.com/hyperf/swoole-tracker) Hyperf 官方提供的對接 Swoole Tracker 的元件，提供阻塞分析、效能分析、記憶體洩漏分析、執行狀態及呼叫統計等功能
- [hyperf/task](https://github.com/hyperf/task) Hyperf 官方提供的 Task 元件，對 Swoole 的 Task 機制進行了封裝及抽象，提供便捷的註解用法

## 開發除錯

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) 通過 `WebSocket` 實時觀測異常錯誤的開發除錯元件

## 第三方 SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) 支援 Swoole 協程的支付寶/微信支付 SDK
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) 支援 Swoole 協程的第三方登入授權 SDK（QQ、微信、微博、Github、Gitee 等）
- [overtrue/wechat](zh-tw/sdks/wechat) EasyWeChat，一個流行的非官方微信 SDK
- [Yurunsoft/PHPMailer-Swoole](https://github.com/Yurunsoft/PHPMailer-Swoole) Swoole 協程環境下的可用的 PHPMailer
