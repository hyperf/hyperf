# Coroutine Component Library

Semua library komponen resmi telah diadaptasi untuk mendukung coroutine dan dapat digunakan dengan aman di dalam Hyperf atau framework coroutine lainnya. Berdasarkan keterbukaan dan ekstensibilitas Hyperf, komunitas dapat mengembangkan atau mengadaptasi berbagai macam komponen. Dengan demikian, Hyperf memiliki potensi yang tak terbatas.
Halaman ini mengumpulkan berbagai komponen coroutine yang diadaptasi untuk Hyperf dan library umum yang sudah terverifikasi aman digunakan di dalam coroutine, sehingga Anda bisa dengan cepat menemukan komponen yang tepat sesuai kebutuhan.

> Urutan komponen diurutkan berdasarkan waktu pencantuman.

## Bagaimana cara mengirimkan komponen saya?

Jika komponen coroutine yang Anda kembangkan diadaptasi untuk Hyperf, maka Anda dapat langsung mengajukan `Pull Request` ke branch `master` dari proyek [hyperf/hyperf](https://github.com/hyperf/hyperf), caranya dengan mengubah halaman ini (`awesome-components.md`).

## Bagaimana cara beradaptasi dengan Hyperf?

Kami menyediakan [Panduan Pengembangan Komponen Hyperf](id/component-guide/intro.md) untuk membantu Anda mengembangkan komponen Hyperf atau beradaptasi dengan framework Hyperf.

# Component List

## Routing

- [nikic/fastroute](https://github.com/nikic/FastRoute) Sebuah router berkecepatan tinggi yang umum digunakan.
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) Sebuah alat URL rewriting berbasis PSR-7 dengan aturan routing yang sama dengan [nikic/fastroute](https://github.com/nikic/FastRoute).

## Events

- [hyperf/event](https://github.com/hyperf/event) Event manager resmi Hyperf berbasis PSR-14.

## Logging

- [hyperf/logger](https://github.com/hyperf/logger) Log manager resmi Hyperf berbasis PSR-3, sebuah abstraksi dan wrapper berbasis monolog.

## Command

- [hyperf/command](https://github.com/hyperf/command) Komponen manajemen command resmi Hyperf berbasis [symfony/console](https://github.com/symfony/console), dikembangkan dengan dukungan Annotations.
- [symfony/console](https://github.com/symfony/console) Komponen manajemen command independen yang disediakan oleh Symfony.

## Database

- [hyperf/database](https://github.com/hyperf/database) Database ORM resmi Hyperf yang berasal dari Eloquent, dapat digunakan kembali di framework lain.
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Komponen caching model otomatis resmi Hyperf yang berbasis komponen [hyperf/database](https://github.com/hyperf/database).
- [reasno/fastmongo](https://github.com/Reasno/fastmongo) Klien `MongoDB` berbasis coroutine yang diimplementasikan berdasarkan `hyperf/gotask`.
- [hyperf-ext/translatable](https://github.com/hyperf-ext/translatable) Menyediakan kemampuan multi-bahasa untuk model.
- [233cy/hyperf-tenant](https://github.com/233cy/hyperf-tenant) Menyediakan pembedaan field multi-tenant untuk model.

## Search Engine

- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Klien Elasticsearch coroutine resmi Hyperf.
- [liangguifeng/hyperf-scout-meilisearch](https://github.com/liangguifeng/hyperf-scout-meilisearch) Driver Meilisearch yang diadaptasi untuk hyperf/scout (mengacu pada laravel/scout).
- [chungou/elasticsearch](https://github.com/kaychem/hyperf-elasticsearch) Sebuah Elasticsearch builder sederhana.

## Dependency Injection Container

- [hyperf/di](https://github.com/hyperf/di) Dependency injection container resmi Hyperf yang mendukung Annotations dan AOP.
- [hyperf/pimple](https://github.com/hyperf-cloud/pimple-integration) Komponen container ringan yang sesuai dengan spesifikasi `PSR11` yang diimplementasikan berdasarkan `pimple/pimple`. Ini memudahkan penggunaan komponen `Hyperf` di framework lain.

## Services

- [hyperf/http-server](https://github.com/hyperf/http-server) HTTP server resmi Hyperf.
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) GRPC server resmi Hyperf.
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) WebSocket server resmi Hyperf.
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) Server abstraksi RPC universal resmi Hyperf.

## Clients

- [hyperf/consul](https://github.com/hyperf/consul) Klien Consul coroutine resmi Hyperf.
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Klien gRPC coroutine resmi Hyperf.
- [hyperf/etcd](https://github.com/hyperf/etcd) Klien ETCD coroutine resmi Hyperf.
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Klien abstraksi RPC universal coroutine resmi Hyperf.
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Klien Guzzle HTTP coroutine resmi Hyperf.
- [hyperf/redis](https://github.com/hyperf/redis) Klien Redis coroutine resmi Hyperf.
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Klien WebSocket coroutine resmi Hyperf.
- [hyperf/cache](https://github.com/hyperf/cache) Klien caching coroutine resmi Hyperf berbasis PSR-16, mendukung penggunaan Annotation.
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) Klien Guzzle HTTP coroutine berbasis Hyperf.
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) Klien OpenAI berbasis Hyperf.

## Message Queue

- [hyperf/amqp](https://github.com/hyperf/amqp) Komponen AMQP coroutine resmi Hyperf.
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Komponen antrian asinkron sederhana berbasis Redis resmi Hyperf.
- [hooklife/hyperf-aliyun-amqp](https://github.com/hooklife/hyperf-aliyun-amqp) Menambahkan dukungan untuk Alibaba Cloud AMQP ke komponen hyperf/amqp.

## Configuration Center

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Komponen akses configuration center Apollo resmi Hyperf.
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Komponen akses layanan konfigurasi aplikasi Alibaba Cloud ACM resmi Hyperf.
- [hyperf/config-etcd](https://github.com/hyperf/config-etcd) Komponen akses configuration center ETCD resmi Hyperf.

## RPC

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Komponen protokol JSON-RPC resmi Hyperf.
- [hyperf/rpc-multiplex](https://github.com/hyperf/rpc-multiplex) Komponen RPC multiplexing resmi Hyperf.
- [hyperf/roc](https://github.com/hyperf/roc) Komponen RPC Server multiplexing versi Golang resmi Hyperf.
- [limingxinleo/roc-skeleton](https://github.com/limingxinleo/roc-skeleton) Skeleton package RPC Server multiplexing versi Golang.

## Service Governance

- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Komponen pembatas laju (rate limiting) resmi Hyperf berdasarkan algoritma token bucket.
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Komponen load balancing resmi Hyperf.
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Komponen service governance resmi Hyperf.
- [hyperf/tracer](https://github.com/hyperf/tracer) Komponen pelacakan rantai panggilan terdistribusi OpenTracing resmi Hyperf.
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Komponen service circuit breaker resmi Hyperf.
- [pudongping/hyperf-throttle-requests](https://github.com/pudongping/hyperf-throttle-requests) Pembatas laju permintaan yang diadaptasi untuk framework Hyperf. Secara fungsional mirip dengan throttle middleware framework Laravel.
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) Komponen [Sentry](https://sentry.io) yang diadaptasi untuk framework Hyperf, digunakan untuk pemantauan exception dan pemantauan performa.

## Scheduled Tasks

- [hyperf/crontab](https://github.com/hyperf/crontab) Komponen tugas terjadwal tingkat detik resmi Hyperf.

## ID Generator

- [hyperf/snowflake](https://github.com/hyperf/snowflake) Komponen generator ID Snowflake resmi Hyperf.
- [tangwei/snowflake](https://github.com/tw2066/snowflake) Berdasarkan komponen `hyperf/snowflake`, dengan pengelolaan `worker machine ID` yang lebih baik.

## Document Generation

- [hyperf/swagger](https://github.com/hyperf/swagger) Komponen pembuatan dokumen Swagger otomatis resmi Hyperf (beta).
- [tangwei/swagger](https://github.com/tw2066/api-docs) Komponen dokumen swagger yang dibuat secara otomatis berdasarkan PHP type (DTO), mendukung pemindaian otomatis saat startup, pembuatan routing (UI) otomatis, dan validasi Annotation.

## Graphql

- [hyperf/graphql](https://github.com/hyperf/graphql) Komponen Graphql server resmi Hyperf (beta).

## Hot Update/Hot Reload

- [hyperf/watcher](id/watcher.md) Komponen hot update resmi.
- [ha-ni-cc/hyperf-watch](https://github.com/ha-ni-cc/hyperf-watch) Komponen hot update umum yang diimplementasikan berdasarkan Swoole.
- [mix-php/swoolefor](https://github.com/mix-php/swoolefor) Komponen hot update umum yang diimplementasikan oleh Mixphp.
- [buexplain/go-watch](https://github.com/buexplain/go-watch) Komponen hot update umum yang diimplementasikan berdasarkan Go language.
- [remy/nodemon](https://github.com/remy/nodemon) Komponen hot update umum yang diimplementasikan berdasarkan node.js.

> Peringatan: Jangan gunakan fungsi `hot update/hot reload` di lingkungan produksi.

## Swoole

- [hyperf/task](https://github.com/hyperf/task) Komponen Task resmi Hyperf, yang mengenkapsulasi dan mengabstraksi mekanisme Task Swoole serta menyediakan penggunaan Annotation yang nyaman.
- [hyperf/gotask](https://github.com/hyperf/gotask) GoTask menjalankan proses Go sebagai sidecar dari proses utama Swoole melalui manajemen proses Swoole. Proses ini menggunakan komunikasi antar-proses untuk mengirim task ke sidecar, memprosesnya, dan menerima nilai kembalian. Ini dapat dipahami sebagai TaskWorker versi Go dari Swoole.

## Development and Debugging

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) Komponen development dan debugging untuk mengamati exception dan error secara real-time melalui `WebSocket`.
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) Mendukung fungsionalitas file konfigurasi multi-env mirip dengan Laravel, misalnya, `APP_ENV=testing` dapat memuat konfigurasi `.env.testing` untuk menimpa `.env` default.
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) Menyediakan fungsi `dump` yang dapat mencetak variabel atau data di dalam program ke jendela command line lain, berdasarkan komponen `Var-Dump Server` milik Symfony.
- [leearvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) Menyediakan shell container Hyperf interaktif berbasis PsySH.
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) Alat debugging yang diadaptasi untuk framework Hyperf.

## Permission Authentication

- [fx/hyperf-http-auth](https://github.com/nfangxu/hyperf-http-auth) Ditulis ulang berdasarkan komponen auth di Laravel, diadaptasi untuk framework Hyperf.
- [96qbhy/hyperf-auth](https://github.com/qbhy/hyperf-auth) Dirancang mengacu pada komponen auth Laravel, mendukung driver jwt, session, dan sso (single point multi-device login).
- [hyperf-ext/jwt](https://github.com/hyperf-ext/jwt) Komponen JWT, mengimplementasikan kemampuan penuh untuk autentikasi JWT.
- [hyperf-ext/auth](https://github.com/hyperf-ext/auth) Di-port dari `illuminate/auth`, hampir sepenuhnya mengimplementasikan fitur fungsional Laravel Auth.
- [donjan-deng/hyperf-casbin](https://github.com/donjan-deng/hyperf-casbin) Framework kontrol akses open-source [Casbin](https://casbin.org/docs/zh-CN/overview) yang diadaptasi untuk Hyperf.

## Testing

- [hyperf/testing](https://github.com/hyperf/testing) Komponen unit testing resmi Hyperf.
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) Plugin [Pest](https://pestphp.com/) yang diadaptasi untuk Hyperf, menyediakan dukungan lingkungan coroutine untuk Pest.

## Distributed Lock

- [lysice/hyperf-redis-lock](https://github.com/Lysice/hyperf-redis-lock) Ditulis ulang berdasarkan komponen lock Laravel, diadaptasi untuk framework Hyperf.
- [pudongping/hyperf-wise-locksmith](https://github.com/pudongping/hyperf-wise-locksmith) Library mutex lock yang diadaptasi untuk framework Hyperf, digunakan untuk menyediakan eksekusi kode PHP yang teratur dalam skenario konkurensi tinggi. Mendukung file locks, distributed locks, red locks, coroutine-level mutex locks.

## Distributed Transaction

- [dtm-php/dtm-client](https://github.com/dtm-php/dtm-client) Komponen client distributed transaction dtm yang mendukung Hyperf.

## Annotation Configuration

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) Mengonfigurasi dependensi dengan cepat menggunakan Annotations, dan mendukung prioritas dependensi.

## DTO

- [fatbit/form-request-param](https://github.com/duncanxia97/hyperf-form-request-param) - Komponen validasi parameter permintaan (validasi form) dengan tipe kuat yang elegan serta komponen Injeksi otomatis berbasis `DTO`.

## Third-party SDK

- [yurunsoft/pay-sdk](https://github.com/Yurunsoft/PaySDK) SDK pembayaran Alipay/WeChat yang mendukung Swoole coroutine.
- [yurunsoft/yurun-oauth-login](https://github.com/Yurunsoft/YurunOAuthLogin) SDK otorisasi login pihak ketiga yang mendukung Swoole coroutine (QQ, WeChat, Weibo, GitHub, Gitee, dll.).
- [w7corp/wechat](sdks/wechat) EasyWeChat, SDK WeChat tidak resmi yang populer.
- [yansongda/hyperf-pay](https://github.com/yansongda/hyperf-pay) Komponen pembayaran yang mendukung `Alipay/WeChat`, diimplementasikan berdasarkan [yansongda/pay](https://github.com/yansongda/pay), diadaptasi untuk framework `Hyperf`.
- [alapi/hyperf-meilisearch](https://github.com/anhao/hyperf-meilisearch) Klien Meilisearch yang disediakan untuk Hyperf Scout.
- [vinchan/message-notify](https://github.com/VinchanGit/message-notify) Komponen notifikasi alarm pemantauan exception Hyperf (robot grup DingTalk, robot grup Lark, email, robot channel QQ, robot grup Enterprise WeChat).
