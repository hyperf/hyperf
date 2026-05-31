# Pendahuluan 3.1

Hyperf adalah framework PHP coroutine berperforma tinggi, sangat fleksibel, dan progresif. Framework ini dilengkapi dengan server coroutine bawaan dan berbagai komponen yang umum digunakan, memberikan peningkatan performa yang signifikan dibandingkan framework tradisional berbasis PHP-FPM. Selain memberikan performa luar biasa, Hyperf juga mempertahankan fleksibilitas dan ekstensibilitas yang tinggi. Komponen standar diimplementasikan berdasarkan [standar PSR](https://www.php-fig.org/psr), dan dengan desain Dependency Injection (DI) yang kuat, sebagian besar komponen atau class dapat diganti dan digunakan kembali.

Pustaka komponen framework ini tidak hanya mencakup `MySQL client` dan `Redis client` berbasis coroutine standar, tetapi juga `Eloquent ORM` (dioptimalkan untuk coroutine), `WebSocket server/client`, `JSON RPC server/client`, `gRPC server/client`, `Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`, `Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`, `Apollo configuration center`, `Aliyun ACM application configuration management`, `ETCD configuration center`, `Rate limiter based on token bucket algorithm`, `General connection pool`, `Circuit breaker`, `Swagger document generation`, `View engine`, `Snowflake global ID generator`, dan lain-lain, sehingga Anda tidak perlu repot-repot mengimplementasikan sendiri versi yang kompatibel dengan coroutine.

Hyperf juga menyediakan fitur-fitur yang sangat praktis seperti `PSR-11 compliant dependency injection container`, `Annotations`, `AOP (Aspect-Oriented Programming)`, `PSR-15 compliant middleware`, `Custom processes`, `PSR-14 compliant event manager`, `Redis/RabbitMQ message queue`, `Automatic model caching`, `PSR-16 compliant caching`, `Crontab second-level scheduled tasks`, `Internationalization (i18n)`, `Validation`, dan lain-lain, yang memenuhi berbagai skenario teknis dan bisnis, serta siap pakai langsung (out-of-the-box).

# Motivasi Framework

Meskipun PHP development frameworks saat ini berkembang dengan pesat, kami belum melihat sebuah framework sempurna yang menggabungkan desain elegan dengan ultra-high performance, belum ada juga yang benar-benar membuka jalan bagi PHP microservices. Inilah motivasi di balik Hyperf dan para anggota timnya. Kami akan terus berinvestasi dan mencurahkan tenaga ke dalam hal ini, dan kami menyambut Anda untuk bergabung bersama kami dalam berpartisipasi pada pengembangan open-source.

# Filosofi Desain

`Hyperspeed + Flexibility = Hyperf`. Dari namanya sendiri, kami telah menetapkan `ultra-high performance` dan `fleksibilitas` sebagai DNA Hyperf.

- Untuk ultra-high performance, kami mendasarkan desain pada Swoole coroutine dan melakukan optimasi ekstensif di dalam framework untuk memastikan output ultra-high performance.
- Untuk fleksibilitas, kami mendasarkan desain pada komponen dependency injection Hyperf yang kuat. Komponen-komponen diimplementasikan berdasarkan kontrak yang sesuai dengan standar PSR dan kontrak yang ditentukan oleh Hyperf, memastikan bahwa sebagian besar komponen atau class dalam framework dapat diganti.

Berdasarkan karakteristik ini, Hyperf memiliki kemungkinan yang sangat luas, seperti implementasi Web service, gateway service, distributed middleware, arsitektur microservice, game server, Internet of Things (IoT), dan lain-lain.

# Production Ready

Kami telah melakukan pengujian unit secara ekstensif untuk memastikan kebenaran logika komponen dan memelihara dokumentasi berkualitas tinggi. Sebelum Hyperf resmi dibuka untuk publik, framework ini telah melalui pengujian ketat di lingkungan produksi. Kami baru membuka proyek ini secara resmi setelah uji coba tersebut. Hingga saat ini, banyak perusahaan internet besar/menengah/kecil yang menggunakan Hyperf di lingkungan produksi mereka.
