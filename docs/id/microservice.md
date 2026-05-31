# Microservices

Microservice adalah layanan-layanan kecil yang otonom dan saling bekerja sama.

## Small, Focused on Doing One Thing Well

Seiring berjalannya waktu, saat kebutuhan terus berulang dan fitur baru ditambahkan, basis kode seringkali tumbuh semakin besar. Bahkan dengan upaya terbaik kita untuk mencapai modularitas yang jelas dalam basis kode yang besar, pada kenyataannya, batasan antar modul sulit untuk didefinisikan. Seiring waktu, kode fungsional yang serupa menjadi tersebar luas, sehingga sulit untuk mengetahui di mana harus melakukan modifikasi saat iterasi, dan semakin sulit untuk memperbaiki bug atau menambahkan fitur baru.
Dalam sistem monolitik, kita biasanya membuat lapisan abstraksi atau menerapkan modularitas untuk memastikan `kohesi` code, sehingga menghindari masalah-masalah yang disebutkan di atas.

> Menurut Robert C. Martin tentang [Single Responsibility Principle](https://baike.baidu.com/item/单一职责原则/9456515): "*Kumpulkan hal-hal yang berubah karena alasan yang sama. Pisahkan hal-hal yang berubah karena alasan yang berbeda.*" Ini menekankan konsep kohesi.

Microservices menerapkan filosofi ini ke dalam layanan-layanan yang independen, menentukan batasan layanan berdasarkan batasan bisnis. Setiap layanan fokus pada apa yang ada di dalam batasannya, sehingga menghindari banyak masalah yang berasal dari basis kode yang terlalu besar.
Jadi, seberapa kecil seharusnya sebuah microservice? Cukup kecil saja, tetapi jangan terlalu kecil. Bagaimana cara mengukur apakah sebuah sistem sudah terpecah cukup kecil? Ketika Anda menghadapi sistem tersebut dan tidak lagi memiliki keinginan untuk "memecahnya" karena terlalu besar, maka itu sudah cukup kecil. Semakin kecil layanannya, semakin jelas pula kelebihan dan kekurangan dari `Microservice architecture`. Layanan yang lebih kecil membawa lebih banyak manfaat dari sisi independensi, namun mengelola banyak layanan juga menjadi lebih kompleks.

## Autonomy

Sebuah microservice adalah entitas independen yang dapat di-deploy secara mandiri dan eksis sebagai proses sistem operasi. Terdapat isolasi antar layanan, dan komunikasi antar layanan terjadi melalui network calls, sehingga memperkuat isolasi dan menghindari tight coupling. Layanan harus dapat dimodifikasi secara independen satu sama lain, dan deployment dari sebuah layanan tidak boleh menyebabkan perubahan pada `Service Consumer`-nya. Hal ini mengharuskan kita untuk mempertimbangkan apa yang harus diekspos oleh `Service Provider` dan apa yang harus disembunyikan. Jika terlalu banyak yang diekspos, `Service Consumer` akan terikat dengan implementasi internal layanan tersebut, yang akan menimbulkan pekerjaan koordinasi tambahan bagi layanan, sehingga mengurangi otonominya.

## Main Benefits

### Technical Heterogeneity

Dalam sebuah sistem yang terdiri dari beberapa layanan yang bekerja sama, Anda dapat memilih teknologi yang paling sesuai untuk setiap layanan. Karena komunikasi antar layanan dilakukan melalui network calls, implementasi sebuah layanan tidak terbatas pada bahasa atau framework implementasi sistem. Ini berarti ketika suatu bagian dari sistem membutuhkan peningkatan performa, bagian tersebut dapat dibangun ulang menggunakan technology stack dengan performa yang lebih baik.

### Resilience

Konsep kunci untuk mencapai sistem yang tangguh adalah `Bulkhead`. Jika sebuah komponen atau layanan dalam sistem menjadi tidak tersedia, hal tersebut tidak boleh menyebabkan cascading failure, sehingga bagian lain dari sistem tetap dapat berfungsi normal. `Service boundary` dari sebuah microservice jelas merupakan `Bulkhead`. Dalam `Monolithic architecture`, khususnya di bawah arsitektur tradisional `PHP-FPM`, jika satu bagian tidak tersedia, dalam banyak kasus, semua fungsionalitas menjadi tidak tersedia. Meskipun Anda dapat menggunakan load balancing dan teknologi lainnya untuk men-deploy sistem di beberapa node guna mengurangi probabilitas sistem menjadi tidak tersedia sepenuhnya, untuk `Microservice architecture`, arsitekturnya sendiri sudah dapat menangani dengan baik ketidaktersediaan layanan dan degradasi fungsional.

### Scalability

Sebuah `Monolithic architecture` hanya dapat di-scale secara keseluruhan, bahkan jika hanya sebagian kecil dari sistem yang memiliki masalah performa. Dengan menggunakan beberapa layanan yang lebih kecil, Anda dapat melakukan scaling hanya pada layanan yang membutuhkan scaling, sehingga layanan yang tidak membutuhkan scaling dapat berjalan di server yang lebih murah, menghemat biaya.

### Simplified Deployment

Dalam `Monolithic architecture` dengan jumlah kode yang sangat besar, bahkan jika Anda hanya mengubah satu baris kode, Anda perlu men-deploy ulang seluruh sistem untuk merilis perubahan tersebut. Deployment semacam itu memiliki dampak yang signifikan dan risiko yang tinggi; oleh karena itu, para pemangku kepentingan enggan untuk men-deploy dengan mudah. Akibatnya, dalam operasi praktis, frekuensi deployment menjadi sangat rendah. Banyak fitur atau `Bugfixes` yang menumpuk di antara versi, dan sejumlah besar perubahan dirilis ke production environment sekaligus. Namun, semakin besar perbedaan antara dua rilis, semakin besar pula kemungkinan terjadinya error.
Tentu saja, dalam pengembangan di bawah arsitektur tradisional `PHP-FPM`, kita mungkin tidak memiliki masalah seperti itu, karena hot update sudah ada secara alami, tetapi pro dan kontra selalu ada bersamaan.

### Alignment with Organizational Structure

Dalam `Monolithic architecture`, terutama ketika struktur tim "terdistribusi" (tersebar secara geografis), konflik kode yang disebabkan oleh banyaknya submit kode dari engineer dan komunikasi iterasi jarak jauh membuat pemeliharaan sistem menjadi lebih kompleks. Kita semua tahu bahwa tim dengan ukuran yang sesuai yang bekerja pada basis kode kecil dapat mencapai produktivitas yang lebih tinggi. Oleh karena itu, pemecahan dan kepemilikan layanan dapat membagi tanggung jawab terkait dengan baik.

### Composability

Salah satu manfaat utama yang diklaim dari `Distributed systems` dan `Service-Oriented Architecture (SOA)` adalah kemudahan dalam menggunakan kembali fungsionalitas yang sudah ada. Di bawah `Microservice architecture`, pemecahan layanan yang lebih granular akan membuat keuntungan ini semakin menonjol.

### High Refactorability

Jika Anda menghadapi sistem `Monolithic architecture` yang besar dengan kode yang kacau dan buruk, semua orang takut untuk me-refactornya dengan mudah. Namun ketika Anda menghadapi layanan berskala kecil dan granular, me-refactor sebuah layanan atau bahkan menulis ulang layanan yang sesuai relatif lebih dapat dilakukan. Bisakah Anda yakin bahwa menghapus ratusan baris kode dalam sistem `Monolithic architecture` yang besar dalam satu hari tidak akan menimbulkan masalah? Namun dalam `Microservice architecture` yang dirancang dengan baik, saya yakin Anda juga dapat menangani penghapusan sebuah layanan dengan mudah.

## No Silver Bullet

Meskipun `Microservice architecture` memiliki banyak manfaat, **Microservices bukanlah silver bullet!!!** Anda perlu menghadapi kompleksitas yang harus dihadapi oleh semua distributed systems. Anda mungkin perlu melakukan banyak pekerjaan dalam deployment, testing, dan monitoring, serta banyak pekerjaan dalam hal inter-service calls dan keandalan layanan. Anda bahkan mungkin perlu menangani masalah seperti distributed transaction atau yang berkaitan dengan CAP. Meskipun `Hyperf` telah menyelesaikan banyak masalah untuk Anda, sebelum menerapkan `Microservice architecture`, tim Anda harus memiliki pengetahuan yang cukup tentang distributed systems untuk menghadapi banyak masalah yang mungkin belum pernah Anda hadapi atau pertimbangkan di bawah `Monolithic architecture`.


*| Beberapa konten dari bab ini diterjemahkan dari buku Sam Newman "Building Microservices"*
