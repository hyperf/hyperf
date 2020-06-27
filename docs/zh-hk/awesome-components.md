# 協程組件庫

所有官方提供的組件庫均已進行協程化處理，可安全地在 Hyperf 內或其它協程框架內使用，基於 Hyperf 的開放性和可擴展性，社區可對此開發或適配各種各樣的組件，得益於此，Hyperf 將存在着無限的可能性。   
本頁將收錄各個適配了 Hyperf 的協程組件 和 已經經過驗證可安全地用於協程下的常用庫，以便您快速的從中選擇合適的組件完成您的需求。

## 如何提交我的組件？

如果您開發的協程組件適配了 Hyperf，那麼您可以直接對 [hyperf/hyperf](https://github.com/hyperf/hyperf) 項目的 `master` 分支發起您的 `Pull Request`，也就是更改當前頁`(zh-cn/awesome-components.md)`。

## 如何適配 Hyperf ?

我們為您提供了一份 [Hyperf 組件開發指南](zh-hk/component-guide/intro.md)，以幫助您開發 Hyperf 組件或適配 Hyperf 框架。

# 組件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一個常用的高速路由

## 事件

- [hyperf/event](https://github.com/hyperf/event) Hyperf 官方提供的基於 PSR-14 的事件管理器

## 日誌

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf 官方提供的基於 PSR-3 的日誌管理器，一個基於 monolog 的抽象及封裝

## 命令

- [hyperf/command](https://github.com/hyperf/command) Hyperf 官方提供的基於 [symfony/console](https://github.com/symfony/console) 擴展並支持註解的命令管理組件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的獨立命令管理組件

## 數據庫

- [hyperf/database](https://github.com/hyperf/database) Hyperf 官方提供的基於 Eloquent 衍生的數據庫 ORM，可複用於其它框架
- [hyperf/model](https://github.com/hyperf/model) Hyperf 官方提供的基於 [hyperf/database](https://github.com/hyperf/database) 組件的自動模型緩存組件 

## 依賴注入容器

- [hyperf/di](https://github.com/hyperf/di) Hyperf 官方提供的支持註解及 AOP 的依賴注入容器

## 服務

- [hyperf/http-server](https://github.com/hyperf/http-server) Hyperf 官方提供的 HTTP 服務端
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) Hyperf 官方提供的 GRPC 服務端
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) Hyperf 官方提供的 WebSocket 服務端
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Hyperf 官方提供的通用 RPC 抽象服務端

## 客户端

- [hyperf/consul](https://github.com/hyperf/consul) Hyperf 官方提供的 Consul 協程客户端
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Hyperf 官方提供的 Elasticsearch 協程客户端
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Hyperf 官方提供的 GRPC 協程客户端
- [hyperf/etcd](https://github.com/hyperf/etcd) Hyperf 官方提供的 ETCD 協程客户端
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Hyperf 官方提供的通用 RPC 抽象協程客户端
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Hyperf 官方提供的 Guzzle HTTP 協程客户端
- [hyperf/redis](https://github.com/hyperf/redis) Hyperf 官方提供的 Redis 協程客户端
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Hyperf 官方提供的 WebSocket 協程客户端
- [hyperf/cache](https://github.com/hyperf/cache) Hyperf 官方提供的基於 PSR-16 的緩存協程客户端，支持註解的使用方式

## 消息隊列

- [hyperf/amqp](https://github.com/hyperf/amqp) Hyperf 官方提供的 AMQP 協程組件
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Hyperf 官方提供的簡單的基於 Redis 的異步隊列組件
- [hooklife/hyperf-aliyun-amqp](https://github.com/hooklife/hyperf-aliyun-amqp) 使 hyperf/amqp 組件支持阿里雲 AMQP

## 配置中心

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Hyperf 官方提供的 Apollo 配置中心接入組件
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Hyperf 官方提供的阿里雲 ACM 應用配置服務接入組件
- [hyperf/config-etcd](https://github.com/hyperf/config-etcd) Hyperf 官方提供的 ETCD 配置中心接入組件

## RPC

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Hyperf 官方提供的 JSON-RPC 協議組件

## 服務治理

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Hyperf 官方提供的基於令牌桶算法的限流組件
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Hyperf 官方提供的負載均衡組件
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Hyperf 官方提供的服務治理組件
- [hyperf/tracer](https://github.com/hyperf/tracer) Hyperf 官方提供的 OpenTracing 分佈式調用鏈追蹤組件
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Hyperf 官方提供的服務熔斷組件

## 定時任務

- [hyperf/crontab](https://github.com/hyperf/crontab) Hyperf 官方提供的秒級定時任務組件

## ID 生成器

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Hyperf 官方提供的 Snowflake ID 生成器組件 (beta)

## 文檔生成

- [hyperf/swagger](https://github.com/hyperf/swagger) Hyperf 官方提供的 Swagger 文檔自動生成組件 (beta)

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf 官方提供的 Graphql 服務端組件 (beta)

## 熱更新/熱重載

- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一個基於 Swoole 實現的通用熱更新組件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一個由 Mixphp 實現的通用熱更新組件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一個基於 Go 語言實現的通用熱更新組件
- [remy/nodemon](https://github.com/remy/nodemon) 一個基於 node.js 實現的通用熱更新組件

> Warning: 請勿於生產環境使用 `熱更新/熱重載` 功能

## Swoole

- [hyperf/swoole-tracker](https://github.com/hyperf/swoole-tracker) Hyperf 官方提供的對接 Swoole Tracker 的組件，提供阻塞分析、性能分析、內存泄漏分析、運行狀態及調用統計等功能
- [hyperf/task](https://github.com/hyperf/task) Hyperf 官方提供的 Task 組件，對 Swoole 的 Task 機制進行了封裝及抽象，提供便捷的註解用法
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask 通過 Swoole 進程管理功能啟動 Go 進程作為 Swoole 主進程邊車(Sidecar)，利用進程通訊將任務投遞給邊車處理並接收返回值。可以理解為 Go 版的 Swoole TaskWorker。

## 開發調試

- [mabu233/sdebug](https://github.com/mabu233/sdebug) 用於協助開發與調試，`xdebug`的協程改造版
- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) 通過 `WebSocket` 實時觀測異常錯誤的開發調試組件
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) 支持與 laravel 類似的多 env 配置文件功能，通過 `APP_ENV=testing` 可以加載 `.env.testing` 配置覆蓋默認的 `.env`

## 權限認證

- [donjan-deng/hyperf-permission](https://github.com/donjan-deng/hyperf-permission) 基於 [spatie/laravel-permission](https://github.com/spatie/laravel-permission) 開發的適配 Hyperf 的權限組件
- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) 根據 laravel 中的 auth 組件改寫的, 適配 hyperf 框架
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) 參考 laravel 的 auth 組件設計，支持 jwt 和 session驅動，更輕巧更好用

## 第三方 SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) 支持 Swoole 協程的支付寶/微信支付 SDK
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) 支持 Swoole 協程的第三方登錄授權 SDK（QQ、微信、微博、Github、Gitee 等）
- [overtrue/wechat](zh-hk/sdks/wechat) EasyWeChat，一個流行的非官方微信 SDK
- [Yurunsoft/PHPMailer-Swoole](https://github.com/Yurunsoft/PHPMailer-Swoole) Swoole 協程環境下的可用的 PHPMailer
