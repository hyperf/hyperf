# Awesome Components

Semua library komponen yang disediakan secara resmi telah diproses agar
mendukung coroutine, sehingga aman digunakan di dalam Hyperf atau framework
coroutine lainnya. Berdasarkan keterbukaan dan skalabilitas Hyperf, komunitas
dapat mengembangkan atau mengadaptasi berbagai komponen untuk ini, dan dengan
memanfaatkan hal tersebut, Hyperf akan memiliki kemungkinan yang tidak terbatas.

Halaman ini mencakup berbagai komponen coroutine yang kompatibel dengan Hyperf
dan library yang umum digunakan yang telah divalidasi dan aman digunakan dalam
coroutine, sehingga Anda dapat dengan cepat memilih komponen yang tepat untuk
memenuhi kebutuhan Anda.

## Bagaimana cara mengirimkan komponen saya?

Jika komponen yang Anda kembangkan diadaptasi untuk Hyperf, Anda dapat langsung
mengirimkan `Pull Request` ke branch `master` dari proyek
[hyperf/hyperf](https://github.com/hyperf/hyperf) untuk mengubah halaman ini
`(id/awesome-components.md)`.

## Bagaimana cara mengadaptasi Hyperf?

Kami telah menyediakan [panduan pengembangan komponen
Hyperf](id/component-guide/intro) untuk membantu Anda mengembangkan komponen
Hyperf atau mengadaptasi komponen ke framework Hyperf.

# Awesome Components

Semua library komponen yang disediakan secara resmi telah diproses agar
mendukung coroutine, sehingga aman digunakan di dalam Hyperf atau framework
coroutine lainnya. Berdasarkan keterbukaan dan skalabilitas Hyperf, komunitas
dapat mengembangkan atau mengadaptasi berbagai komponen untuk ini, dan dengan
memanfaatkan hal tersebut, Hyperf akan memiliki kemungkinan yang tidak terbatas.

Halaman ini mencakup berbagai komponen coroutine yang kompatibel dengan Hyperf
dan library yang umum digunakan yang telah divalidasi dan aman digunakan dalam
coroutine, sehingga Anda dapat dengan cepat memilih komponen yang tepat untuk
memenuhi kebutuhan Anda.

## Bagaimana cara mengirimkan komponen saya?

Jika komponen yang Anda kembangkan diadaptasi untuk Hyperf, Anda dapat langsung
mengirimkan `Pull Request` ke branch `master` dari proyek
[hyperf/hyperf](https://github.com/hyperf/hyperf) untuk mengubah halaman ini
`(id/awesome-components.md)`.

## Bagaimana cara mengadaptasi Hyperf?

Kami telah menyediakan [panduan pengembangan komponen
Hyperf](id/component-guide/intro) untuk membantu Anda mengembangkan komponen
Hyperf atau mengadaptasi komponen ke framework Hyperf.

# Daftar komponen

## Route

- [nikic/fastroute](https://github.com/nikic/FastRoute) routing berkecepatan
  tinggi yang umum digunakan
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) Alat URL
  rewriting berbasis PSR-7 dengan aturan routing yang sama dengan
  [nikic/fastroute](https://github.com/nikic/FastRoute)

## Event

- [hyperf/event](https://github.com/hyperf/event) Event manager berbasis PSR-14
  yang disediakan secara resmi oleh Hyperf

## Log

- [hyperf/logger](https://github.com/hyperf/logger) Log manager berbasis PSR-3
  yang disediakan secara resmi oleh Hyperf

## Command

- [hyperf/command](https://github.com/hyperf/command) Komponen manajemen
  command berbasis ekstensi [symfony/console](https://github.com/symfony/console)
  dan mendukung annotation, disediakan secara resmi oleh Hyperf
- [symfony/console](https://github.com/symfony/console) Komponen manajemen
  command independen yang disediakan oleh Symfony

## Database

- [hyperf/database](https://github.com/hyperf/database) Berbasis Eloquent
  database ORM yang di-fork oleh Hyperf, komponen ini dapat digunakan kembali
  pada framework lain
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Komponen caching
  model otomatis berbasis komponen [hyperf/database](https://github.com/hyperf/database)
  yang disediakan secara resmi oleh Hyperf

## Dependency injection container

- [hyperf/di](https://github.com/hyperf/di) Dependency injection container yang
  disediakan secara resmi oleh Hyperf, mendukung annotation dan AOP

## Server

- [hyperf/http-server](https://github.com/hyperf/http-server) HTTP server yang
  disediakan secara resmi oleh Hyperf
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) gRPC server yang
  disediakan secara resmi oleh Hyperf
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) WebSocket
  server yang disediakan secara resmi oleh Hyperf
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) RPC server abstrak
  yang disediakan secara resmi oleh Hyperf

## Client

- [hyperf/consul](https://github.com/hyperf/consul) Coroutine client Consul
  yang disediakan secara resmi oleh Hyperf
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) Coroutine
  client Elasticsearch yang disediakan secara resmi oleh Hyperf
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) Coroutine client
  gRPC yang disediakan secara resmi oleh Hyperf
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) Coroutine client
  RPC abstrak yang disediakan secara resmi oleh Hyperf
- [hyperf/guzzle](https://github.com/hyperf/guzzle) Coroutine client HTTP
  Guzzle yang disediakan secara resmi oleh Hyperf
- [hyperf/redis](https://github.com/hyperf/redis) Coroutine client Redis yang
  disediakan secara resmi oleh Hyperf
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) Coroutine
  client WebSocket yang disediakan secara resmi oleh Hyperf
- [hyperf/cache](https://github.com/hyperf/cache) Coroutine client cache
  berbasis PSR-16 yang disediakan secara resmi oleh Hyperf
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client)
  Coroutine client HTTP Guzzle berbasis Hyperf
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client)
  Coroutine client OpenAI berbasis Hyperf

## Testing

- [hyperf/testing](https://github.com/hyperf/testing) Komponen unit testing
  resmi dari Hyperf
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf)
  Plugin [Pest](https://pestphp.com/) yang dirancang khusus untuk Hyperf,
  menyediakan dukungan lingkungan coroutine untuk Pest.

## Message queue

- [hyperf/amqp](https://github.com/hyperf/amqp) Komponen coroutine AMQP yang
  disediakan secara resmi oleh Hyperf
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Komponen
  asynchronous queue berbasis Redis yang disediakan secara resmi oleh Hyperf

## Configuration center

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Komponen pusat
  konfigurasi Apollo yang disediakan secara resmi oleh Hyperf
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Komponen
  layanan konfigurasi aplikasi ACM Aliyun yang disediakan secara resmi oleh
  Hyperf

## Service governance

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Komponen protokol
  JSON-RPC yang disediakan secara resmi oleh Hyperf
- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Komponen rate
  limiter berbasis algoritma token bucket yang disediakan secara resmi oleh Hyperf
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Komponen load
  balancer yang disediakan secara resmi oleh Hyperf
- [hyperf/service-governance](https://github.com/hyperf/service-governance)
  Komponen service governance yang disediakan secara resmi oleh Hyperf
- [hyperf/tracer](https://github.com/hyperf/tracer) Komponen OpenTracing yang
  disediakan secara resmi oleh Hyperf
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Komponen
  circuit breaker layanan yang disediakan secara resmi oleh Hyperf
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) Komponen
  [Sentry](https://sentry.io) berbasis Hyperf

## Annotation Configuration

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency)
  Menggunakan annotation untuk mengonfigurasi dependency dengan cepat dan
  mendukung prioritas dependency.

## DTO

- [fatbit/form-request-param](https://github.com/duncanxia97/hyperf-form-request-param)
  - Komponen validasi parameter request bertipe kuat (validasi form) berbasis
  `DTO` dan injeksi otomatis yang elegan.

## Development and debugging

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) Komponen pengembangan
  dan debugging untuk memantau error abnormal secara real-time melalui `WebSocket`
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) Mendukung
  fungsi file konfigurasi multi-env yang mirip dengan Laravel, seperti
  `APP_ENV=testing` dapat memuat konfigurasi `.env.testing` untuk menimpa
  `.env` default
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server)
  Menyediakan fungsi `dump` yang dapat mencetak variabel atau data program ke
  jendela command line lain, berbasis komponen `Var-Dump Server` Symfony
- [learvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) Menyediakan
  wadah shell Hyperf interaktif berbasis PsySH
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) Alat
  debugging yang diadaptasi untuk Hyperf
