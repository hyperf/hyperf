# Pendahuluan
> **Catatan:** Untuk informasi lebih lengkap, lihat versi bahasa Mandarin di [README (zh‑cn)](../zh-cn/README.md).

Hyperf adalah framework PHP CLI yang sangat performan dan fleksibel, ditenagai
oleh coroutine server canggih dan sejumlah besar komponen yang telah teruji
dalam pertempuran. Selain mengalahkan framework PHP-FPM secara meyakinkan dalam
benchmark, Hyperf unik karena fokusnya pada fleksibilitas dan komposisi. Hyperf
hadir dengan dependency injector berkemampuan AOP (aspect-oriented programming)
untuk memastikan komponen dan kelas bersifat pluggable dan meta-programmable.
Semua komponen inti Hyperf secara ketat mengikuti standar
[PSR](https://www.php-fig.org/psr) dan dapat digunakan di framework lain.

Arsitektur Hyperf dibangun menggunakan kombinasi `Coroutines`,
`Dependency injection`, `Events`, `Annotations`, dan `AOP`. Selain menyediakan
client coroutine umum seperti `MySQL` dan `Redis`, `Hyperf` juga menyediakan
versi yang kompatibel dengan coroutine untuk `WebSocket server / client`,
`JSON RPC server / client`, `gRPC server / client`,
`Zipkin/Jaeger (OpenTracing) client`, `Guzzle HTTP client`,
`Elasticsearch client`, `Consul client`, `ETCD client`, `AMQP component`,
`Apollo configuration center`, `Aliyun ACM`, `ETCD configuration center`,
`Token bucket algorithm-based limiter`, `Universal connection pool`,
`Circuit breaker`, `Swagger`, `Snowflake`, `Simply Redis MQ`, `RabbitMQ`, `NSQ`,
`Nats`, `Seconds level crontab`, `Custom Processes`, dll. Oleh karena itu,
developer dapat sepenuhnya menghindari implementasi versi yang kompatibel dengan
coroutine dari library-library ini.

Tenang saja, Hyperf tetap merupakan sebuah framework PHP. Hyperf menyediakan
semua package yang Anda harapkan: `Middleware`, `Event Manager`,
`Coroutine-optimized Eloquent ORM` (dan Model Cache!), `Translation`,
`Validation`, `View engine (Blade/Smarty/Twig/Plates/ThinkTemplate)` dan
banyak lagi.

# Asal-usul

Meskipun ada banyak framework PHP baru, kami masih belum menemukan framework
yang memadukan desain elegan dengan performa ultra-tinggi, kami juga belum
menemukan framework yang merintis jalan untuk microservices PHP. Dengan visi
ini, kami akan terus berinvestasi dalam masa depan framework ini, dan Anda
sangat disambut untuk bergabung bersama kami dalam berkontribusi pada
pengembangan open-source Hyperf.

# Tujuan Desain

`Hyperspeed + Flexibility = Hyperf`. Persamaan yang tersembunyi dalam nama
kami menunjukkan ambisi awal pendirian Hyperf.

Hyperspeed: Memanfaatkan coroutine `Swoole` dan `Swow`, Hyperf mampu menangani
lalu lintas dalam jumlah masif. Tim Hyperf melakukan banyak optimasi pada
framework untuk mengeliminasi setiap bottleneck antara end-user dan engine
kami yang sangat cepat.

Flexibility: Kami percaya komponen Dependency Injection kami adalah yang
terbaik di kelasnya. Dengan bantuan `Hyperf DI`, semua komponen dan kelas
bersifat pluggable dan meta-programmable. Sebaliknya, semua komponen Hyperf
dimaksudkan untuk dibagikan kepada dunia. Komitmen kami terhadap standar PSR
berarti Anda dapat menggunakan komponen Hyperf di framework apa pun yang
kompatibel.

Melalui karakteristik ini, Hyperf telah menemukan potensi terpendam di
banyak bidang: mengimplementasikan Web server, gateway server, software
middleware terdistribusi, arsitektur microservices, game server, dan
Internet-of-Things (IoT).

# Siap untuk Produksi

Bersamaan dengan dokumentasi multibahasa kami yang terpelihara dengan baik,
sejumlah besar unit test untuk setiap komponen memastikan kebenaran logisnya.
Sebelum `Hyperf` dirilis ke publik (20-06-2019), ia telah digunakan secara
pribadi oleh beberapa perusahaan internet menengah dan besar untuk berbagai
layanan, yang telah berjalan tanpa insiden selama bertahun-tahun di lingkungan
produksi yang keras.
