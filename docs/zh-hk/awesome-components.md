# 協程組件庫

所有官方提供的組件庫均已進行協程化處理，可安全地在 Hyperf 內或其它協程框架內使用，基於 Hyperf 的開放性和可擴展性，社區可對此開發或適配各種各樣的組件，得益於此，Hyperf 將存在着無限的可能性。
本頁將收錄各個適配了 Hyperf 的協程組件和已經經過驗證可安全地用於協程下的常用庫，以便您快速的從中選擇合適的組件完成您的需求。

> 組件順序以收錄時間排序

## 如何提交我的組件？

如果您開發的協程組件適配了 Hyperf，那麼您可以直接對 [hyperf/hyperf](https://github.com/hyperf/hyperf) 項目的 `master` 分支發起您的 `Pull Request`，也就是更改當前頁`(zh-cn/awesome-components.md)`。

## 如何適配 Hyperf ?

我們為您提供了一份 [Hyperf 組件開發指南](zh-hk/component-guide/intro.md)，以幫助您開發 Hyperf 組件或適配 Hyperf 框架。

# 組件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一個常用的高速路由
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) 一個基於 PSR- 7 與 [nikic/fastroute](https://github.com/nikic/FastRoute) 相同路由規則的 URL 重寫工具

## 事件

- [hyperf/event](https://github.com/hyperf/event) Hyperf 官方提供的基於 PSR-14 的事件管理器

## 日誌

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf 官方提供的基於 PSR-3 的日誌管理器，一個基於 monolog 的抽象及封裝

## 命令

- [hyperf/command](https://github.com/hyperf/command) Hyperf 官方提供的基於 [symfony/console](https://github.com/symfony/console) 擴展並支持註解的命令管理組件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的獨立命令管理組件

## 數據庫

- [hyperf/database](https://github.com/hyperf/database) Hyperf 官方提供的基於 Eloquent 衍生的數據庫 ORM，可複用於其它框架
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Hyperf 官方提供的基於 [hyperf/database](https://github.com/hyperf/database) 組件的自動模型緩存組件
- [reasno/fastmongo](https://github.com/Reasno/fastmongo) 基於 `hyperf/gotask` 實現的協程化 `MongoDB` 客户端
- [hyperf-ext/translatable](https://github.com/hyperf-ext/translatable) 為模型提供多語言能力
- [233cy/hyperf-tenant](https://github.com/233cy/hyperf-tenant) 為模型提供多租户字段區分

## 依賴注入容器

- [hyperf/di](https://github.com/hyperf/di) Hyperf 官方提供的支持註解及 AOP 的依賴注入容器
- [hyperf/pimple](https://github.com/hyperf-cloud/pimple-integration) 基於 `pimple/pimple` 實現的輕量級符合 `PSR11` 規範的容器組件。可以減少其他框架使用 `Hyperf` 組件時的成本

## 服務

- [hyperf/http-server](https://github.com/hyperf/http-server) Hyperf 官方提供的 HTTP 服務端
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) Hyperf 官方提供的 GRPC 服務端
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) Hyperf 官方提供的 WebSocket 服務端
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Hyperf 官方提供的通用 RPC 抽象服務端

## 客户端

- [hyperf/consul](https://github.com/hyperf/consul) Hyperf 官方提供的 Consul 協程客户端
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Hyperf 官方提供的 Elasticsearch 協程客户端
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Hyperf 官方提供的 gRPC 協程客户端
- [hyperf/etcd](https://github.com/hyperf/etcd) Hyperf 官方提供的 ETCD 協程客户端
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Hyperf 官方提供的通用 RPC 抽象協程客户端
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Hyperf 官方提供的 Guzzle HTTP 協程客户端
- [hyperf/redis](https://github.com/hyperf/redis) Hyperf 官方提供的 Redis 協程客户端
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Hyperf 官方提供的 WebSocket 協程客户端
- [hyperf/cache](https://github.com/hyperf/cache) Hyperf 官方提供的基於 PSR-16 的緩存協程客户端，支持註解的使用方式
- [chungou/elasticsearch](https://github.com/kaychem/hyperf-elasticsearch) 一個簡單的 Elasticsearch 構造器
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) 基於 Hyperf 的 Guzzle HTTP 協程客户端
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) 基於 Hyperf 的 OpenAI 客户端

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
- [hyperf/rpc-multiplex](https://github.com/hyperf/rpc-multiplex) Hyperf 官方提供的多路複用 RPC 組件
- [hyperf/roc](https://github.com/hyperf/roc) Hyperf 官方提供的 Golang 版本的多路複用 RPC Server 組件
- [limingxinleo/roc-skeleton](https://github.com/limingxinleo/roc-skeleton) Golang 版本多路複用 RPC Server 骨架包

## 服務治理

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Hyperf 官方提供的基於令牌桶算法的限流組件
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Hyperf 官方提供的負載均衡組件
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Hyperf 官方提供的服務治理組件
- [hyperf/tracer](https://github.com/hyperf/tracer) Hyperf 官方提供的 OpenTracing 分佈式調用鏈追蹤組件
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Hyperf 官方提供的服務熔斷組件
- [pudongping/hyperf-throttle-requests](https://github.com/pudongping/hyperf-throttle-requests) 適配 Hyperf 框架的請求頻率限流器。功能類似於 Laravel 框架的 throttle 中間件。
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) 適配 Hyperf 框架的 [Sentry](https://sentry.io) 組件，用於異常監控與性能監控。

## 定時任務

- [hyperf/crontab](https://github.com/hyperf/crontab) Hyperf 官方提供的秒級定時任務組件

## ID 生成器

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Hyperf 官方提供的 Snowflake ID 生成器組件
- [tangwei/snowflake](https://github.com/tw2066/snowflake) 基於`hyperf/snowflake`組件，增強了`工作機器ID`的維護

## 文檔生成

- [hyperf/swagger](https://github.com/hyperf/swagger) Hyperf 官方提供的 Swagger 文檔自動生成組件 (beta)
- [tangwei/swagger](https://github.com/tw2066/api-docs) 一個基於 PHP 類型(DTO)自動生成 swagger 文檔組件，啓動自動掃描、自動生成路由(UI)、註解驗證

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf 官方提供的 Graphql 服務端組件 (beta)

## 熱更新/熱重載

- [hyperf/watcher](zh-hk/watcher.md) 官方熱更新組件
- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一個基於 Swoole 實現的通用熱更新組件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一個由 Mixphp 實現的通用熱更新組件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一個基於 Go 語言實現的通用熱更新組件
- [remy/nodemon](https://github.com/remy/nodemon) 一個基於 node.js 實現的通用熱更新組件

> Warning: 請勿於生產環境使用 `熱更新/熱重載` 功能

## Swoole

- [hyperf/task](https://github.com/hyperf/task) Hyperf 官方提供的 Task 組件，對 Swoole 的 Task 機制進行了封裝及抽象，提供便捷的註解用法
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask 通過 Swoole 進程管理功能啓動 Go 進程作為 Swoole 主進程邊車(Sidecar)，利用進程通訊將任務投遞給邊車處理並接收返回值。可以理解為 Go 版的 Swoole TaskWorker

## 開發調試

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) 通過 `WebSocket` 實時觀測異常錯誤的開發調試組件
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) 支持與 laravel 類似的多 env 配置文件功能，比如通過 `APP_ENV=testing` 可以加載 `.env.testing` 配置覆蓋默認的 `.env`
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) 提供一個 `dump` 函數，可以將程序內的變量或數據打印到另一個命令行窗口中，基於 Symfony 的 `Var-Dump Server` 組件
- [leearvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) 基於 PsySH 提供一個交互式的 Hyperf shell 容器
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) 適配 Hyperf 框架的調試工具。

## 權限認證

- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) 根據 laravel 中的 auth 組件改寫的, 適配 hyperf 框架
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) 參考 laravel 的 auth 組件設計，支持 jwt、session、sso(單點多設備登錄) 驅動
- [hyperf-ext/jwt](https://github.com/hyperf-ext/jwt) JWT 組件，實現了完整用於 JWT 認證的能力
- [hyperf-ext/auth](https://github.com/hyperf-ext/auth) 移植自 `illuminate/auth`，基本完整的實現了 Laravel Auth 的功能特性
- [donjan-deng/hyperf-casbin](https://github.com/donjan-deng/hyperf-casbin) 適配於 Hyperf 的開源訪問控制框架 [Casbin](https://casbin.org/docs/zh-CN/overview)

## 測試

- [hyperf/testing](https://github.com/hyperf/testing) Hyperf 官方提供的單元測試組件
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) 適配於 Hyperf 的 [Pest](https://pestphp.com/) 插件，為 Pest 提供協程環境支持

## 分佈式鎖

- [lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) 根據 Laravel 的 lock 組件改寫，適配於 Hyperf 框架
- [pudongping/hyperf-wise-locksmith](https://github.com/pudongping/hyperf-wise-locksmith) 適配 hyperf 框架的互斥鎖庫，用於在高併發場景下提供 PHP 代碼的有序執行。支持文件鎖、分佈式鎖、紅鎖、協程級互斥鎖

## 分佈式事務

- [dtm-php/dtm-client](https://github.com/dtm-php/dtm-client) 支持 Hyperf 的 dtm 分佈式事務客户端組件

## 註解配置

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) 使用註解快速的配置依賴關係，並且支持依賴優先級

## 第三方 SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) 支持 Swoole 協程的支付寶/微信支付 SDK
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) 支持 Swoole 協程的第三方登錄授權 SDK（QQ、微信、微博、GitHub、Gitee 等）
- [w7corp/wechat](zh-hk/sdks/wechat) EasyWeChat，一個流行的非官方微信 SDK
- [yansongda/hyperf-pay](https://github.com/yansongda/hyperf-pay) 支持 `支付寶/微信` 的支付組件，基於 [yansongda/pay](https://github.com/yansongda/pay) 實現，適配於 `Hyperf` 框架
- [alapi/hyperf-meilisearch](https://github.com/anhao/hyperf-meilisearch) 為 Hyperf Scout 提供的 meilisearch 客户端
- [vinchan/message-notify](https://github.com/VinchanGit/message-notify) Hyperf 異常監控報警通知組件(釘釘羣機器人、飛書羣機器人、郵件、QQ 頻道機器人、企業微信羣機器人)
