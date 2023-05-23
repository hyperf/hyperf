# 協程元件庫

所有官方提供的元件庫均已進行協程化處理，可安全地在 Hyperf 內或其它協程框架內使用，基於 Hyperf 的開放性和可擴充套件性，社群可對此開發或適配各種各樣的元件，得益於此，Hyperf 將存在著無限的可能性。
本頁將收錄各個適配了 Hyperf 的協程元件和已經經過驗證可安全地用於協程下的常用庫，以便您快速的從中選擇合適的元件完成您的需求。

> 元件順序以收錄時間排序

## 如何提交我的元件？

如果您開發的協程元件適配了 Hyperf，那麼您可以直接對 [hyperf/hyperf](https://github.com/hyperf/hyperf) 專案的 `master` 分支發起您的 `Pull Request`，也就是更改當前頁`(zh-cn/awesome-components.md)`。

## 如何適配 Hyperf ?

我們為您提供了一份 [Hyperf 元件開發指南](zh-tw/component-guide/intro.md)，以幫助您開發 Hyperf 元件或適配 Hyperf 框架。

# 元件列表

## 路由

- [nikic/fastroute](https://github.com/nikic/FastRoute) 一個常用的高速路由
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) 一個基於PSR-7與 [nikic/fastroute](https://github.com/nikic/FastRoute) 相同路由規則的URL重寫工具

## 事件

- [hyperf/event](https://github.com/hyperf/event) Hyperf 官方提供的基於 PSR-14 的事件管理器

## 日誌

- [hyperf/logger](https://github.com/hyperf/logger) Hyperf 官方提供的基於 PSR-3 的日誌管理器，一個基於 monolog 的抽象及封裝

## 命令

- [hyperf/command](https://github.com/hyperf/command) Hyperf 官方提供的基於 [symfony/console](https://github.com/symfony/console) 擴充套件並支援註解的命令管理元件
- [symfony/console](https://github.com/symfony/console) Symfony 提供的獨立命令管理元件

## 資料庫

- [hyperf/database](https://github.com/hyperf/database) Hyperf 官方提供的基於 Eloquent 衍生的資料庫 ORM，可複用於其它框架
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Hyperf 官方提供的基於 [hyperf/database](https://github.com/hyperf/database) 元件的自動模型快取元件
- [reasno/fastmongo](https://github.com/Reasno/fastmongo) 基於 `hyperf/gotask` 實現的協程化 `MongoDB` 客戶端
- [hyperf-ext/translatable](https://github.com/hyperf-ext/translatable) 為模型提供多語言能力

## 依賴注入容器

- [hyperf/di](https://github.com/hyperf/di) Hyperf 官方提供的支援註解及 AOP 的依賴注入容器
- [hyperf/pimple](https://github.com/hyperf-cloud/pimple-integration) 基於 `pimple/pimple` 實現的輕量級符合 `PSR11` 規範的容器元件。可以減少其他框架使用 `Hyperf` 元件時的成本

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
- [chungou/elasticsearch](https://github.com/kaychem/hyperf-elasticsearch) 一個簡單的 Elasticsearch 構造器
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) 基於 Hyperf 的 Guzzle HTTP 協程客戶端
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) 基於 Hyperf 的 OpenAI 客戶端

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
- [hyperf/rpc-multiplex](https://github.com/hyperf/rpc-multiplex) Hyperf 官方提供的多路複用 RPC 元件
- [hyperf/roc](https://github.com/hyperf/roc) Hyperf 官方提供的 Golang 版本的多路複用 RPC Server 元件
- [limingxinleo/roc-skeleton](https://github.com/limingxinleo/roc-skeleton) Golang 版本多路複用 RPC Server 骨架包

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
- [tangwei/swagger](https://github.com/tw2066/api-docs) 一個基於 PHP 型別(DTO)自動生成 swagger 文件元件，啟動自動掃描、自動生成路由(UI)、註解驗證

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Hyperf 官方提供的 Graphql 服務端元件 (beta)

## 熱更新/熱過載

- [hyperf/watcher](zh-tw/watcher.md) 官方熱更新元件
- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) 一個基於 Swoole 實現的通用熱更新元件
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) 一個由 Mixphp 實現的通用熱更新元件
- [buexplain/go-watch](https://github.com/buexplain/go-watch) 一個基於 Go 語言實現的通用熱更新元件
- [remy/nodemon](https://github.com/remy/nodemon) 一個基於 node.js 實現的通用熱更新元件

> Warning: 請勿於生產環境使用 `熱更新/熱過載` 功能

## Swoole

- [hyperf/swoole-tracker](https://github.com/hyperf/swoole-tracker) Hyperf 官方提供的對接 Swoole Tracker 的元件，提供阻塞分析、效能分析、記憶體洩漏分析、執行狀態及呼叫統計等功能
- [hyperf/task](https://github.com/hyperf/task) Hyperf 官方提供的 Task 元件，對 Swoole 的 Task 機制進行了封裝及抽象，提供便捷的註解用法
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask 透過 Swoole 程序管理功能啟動 Go 程序作為 Swoole 主程序邊車(Sidecar)，利用程序通訊將任務投遞給邊車處理並接收返回值。可以理解為 Go 版的 Swoole TaskWorker

## 開發除錯

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) 透過 `WebSocket` 實時觀測異常錯誤的開發除錯元件
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) 支援與 laravel 類似的多 env 配置檔案功能，比如透過 `APP_ENV=testing` 可以載入 `.env.testing` 配置覆蓋預設的 `.env`
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) 提供一個 `dump` 函式，可以將程式內的變數或資料列印到另一個命令列視窗中，基於 Symfony 的 `Var-Dump Server` 元件
- [leearvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) 基於 PsySH 提供一個互動式的 Hyperf shell 容器

## 許可權認證

- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) 根據 laravel 中的 auth 元件改寫的, 適配 hyperf 框架
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) 參考 laravel 的 auth 元件設計，支援 jwt、session、sso(單點多裝置登入) 驅動
- [hyperf-ext/jwt](https://github.com/hyperf-ext/jwt) JWT 元件，實現了完整用於 JWT 認證的能力
- [hyperf-ext/auth](https://github.com/hyperf-ext/auth) 移植自 `illuminate/auth`，基本完整的實現了 Laravel Auth 的功能特性
- [donjan-deng/hyperf-casbin](https://github.com/donjan-deng/hyperf-casbin) 適配於 Hyperf 的開源訪問控制框架 [Casbin](https://casbin.org/docs/zh-CN/overview)

## 分散式鎖

- [lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) 根據 Laravel 的 lock 元件改寫，適配於 Hyperf 框架

## 分散式事務

- [dtm-php/dtm-client](https://github.com/dtm-php/dtm-client) 支援 Hyperf 的 dtm 分散式事務客戶端元件

## 註解配置

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) 使用註解快速的配置依賴關係，並且支援依賴優先順序

## 第三方 SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) 支援 Swoole 協程的支付寶/微信支付 SDK
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) 支援 Swoole 協程的第三方登入授權 SDK（QQ、微信、微博、GitHub、Gitee 等）
- [w7corp/wechat](zh-tw/sdks/wechat) EasyWeChat，一個流行的非官方微信 SDK
- [yansongda/hyperf-pay](https://github.com/yansongda/hyperf-pay) 支援 `支付寶/微信` 的支付元件，基於 [yansongda/pay](https://github.com/yansongda/pay) 實現，適配於 `Hyperf` 框架
- [alapi/hyperf-meilisearch](https://github.com/anhao/hyperf-meilisearch) 為 Hyperf Scout 提供的 meilisearch 客戶端
- [vinchan/message-notify](https://github.com/VinchanGit/message-notify) Hyperf 異常監控報警通知元件(釘釘群機器人、飛書群機器人、郵件、QQ 頻道機器人、企業微信群機器人)
