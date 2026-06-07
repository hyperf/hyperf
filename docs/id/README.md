# Pendahuluan 3.1

Hyperf adalah framework PHP coroutine progresif yang menggabungkan performa tinggi dengan fleksibilitas luar biasa. Framework ini hadir dengan server coroutine bawaan dan beragam komponen siap pakai, menawarkan peningkatan performa yang signifikan dibandingkan framework tradisional berbasis PHP-FPM. Selain kencang, Hyperf juga tetap sangat fleksibel dan mudah dikembangkan. Semua komponen standar dibangun di atas [standar PSR](https://www.php-fig.org/psr), dan berkat desain Dependency Injection (DI) yang kuat, sebagian besar komponen atau class bisa diganti dan digunakan kembali sesuai kebutuhan.

Selain `MySQL client` dan `Redis client` berbasis coroutine standar, pustaka komponen Hyperf juga mencakup `Eloquent ORM` (dioptimalkan untuk coroutine), `WebSocket server/client`, `JSON RPC server/client`, `gRPC server/client`, `Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Aliyun ACM application configuration management`, `ETCD configuration center`, `Rate limiter based on token bucket algorithm`, `General connection pool`, `Circuit breaker`, `Swagger document generation`, `View engine`, `Snowflake global ID generator`, dan masih banyak lagi, sehingga Anda tidak perlu lagi membuat versi coroutine dari pustaka-pustaka tersebut secara manual.

Hyperf juga menyediakan berbagai fitur praktis seperti `PSR-11 compliant dependency injection container`, `Annotations`, `AOP (Aspect-Oriented Programming)`, `PSR-15 compliant middleware`, `Custom processes`, `PSR-14 compliant event manager`, `Redis/RabbitMQ message queue`, `Automatic model caching`, `PSR-16 compliant caching`, `Crontab second-level scheduled tasks`, `Internationalization (i18n)`, `Validation`, dan lain-lain, yang siap memenuhi beragam kebutuhan teknis dan bisnis secara langsung (out-of-the-box).

# Motivasi Framework

Meskipun ekosistem PHP framework berkembang pesat, kami masih belum menemukan framework yang mampu menggabungkan desain elegan dengan performa ultra-high, sekaligus membuka jalan bagi microservice PHP. Inilah motivasi utama Hyperf dan tim di baliknya. Kami akan terus mengembangkan dan berinvestasi pada framework ini, dan Anda dipersilakan untuk turut berkontribusi dalam pengembangan open-source Hyperf.

# Filosofi Desain

`Hyperspeed + Flexibility = Hyperf`. Dari namanya saja, `kecepatan ultra-high` dan `fleksibilitas` sudah menjadi DNA Hyperf.

- Untuk kecepatan ultra-high, desain kami bertumpu pada Swoole coroutine dengan berbagai optimasi ekstensif di dalam framework demi menghasilkan output performa yang maksimal.
- Untuk fleksibilitas, desain kami bertumpu pada komponen dependency injection Hyperf yang powerful. Semua komponen dibangun di atas kontrak yang sesuai dengan standar PSR maupun kontrak bawaan Hyperf, sehingga hampir seluruh komponen atau class dalam framework bisa diganti sesuai kebutuhan.

Berkat karakteristik ini, Hyperf bisa digunakan di berbagai skenario: Web service, gateway service, distributed middleware, arsitektur microservice, game server, Internet of Things (IoT), dan masih banyak lagi.

# Production Ready

Setiap komponen telah melalui pengujian unit yang ekstensif untuk memastikan kebenaran logika, dan dokumentasi berkualitas tinggi selalu kami jaga. Sebelum dirilis ke publik, Hyperf telah melewati pengujian ketat di lingkungan produksi, proyek ini baru dibuka setelah benar-benar siap. Hingga saat ini, puluhan perusahaan internet skala besar, menengah, maupun kecil telah menggunakan Hyperf di lingkungan produksi mereka.
