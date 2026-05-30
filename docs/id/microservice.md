# Microservice

Microservice adalah service kecil dan otonom yang bekerja bersama-sama.

## Kecil, fokus melakukan satu hal dengan baik

Seiring dengan iterasi kebutuhan dan bertambahnya fitur-fitur baru, repository
kode cenderung menjadi semakin besar. Meskipun kita sangat berharap untuk
mencapai modularitas yang jelas dalam repository kode yang besar tersebut, pada
kenyataannya batas-batas antar-modul sulit untuk dibedakan secara jelas. Lambat
laun, kode dengan fungsi serupa dapat terlihat di mana-mana di dalam repository
kode. Akibatnya, sangat sulit untuk mengetahui di mana perubahan harus dilakukan
saat ada pembaruan versi, dan semakin sulit pula untuk memperbaiki `Bug` serta
menambahkan fitur baru.
Dalam mono-system (sistem tunggal), beberapa lapisan abstraksi atau
modularisasi biasanya dibuat untuk memastikan `cohesion` dari kode, sehingga
menghindari masalah yang disebutkan di atas.

> Menurut Robert C. Martin [Single Responsibility Principle](https://baike.baidu.com/item/单一职责原则/9456515): "* Kumpulkan hal-hal yang berubah karena alasan yang sama secara bersama-sama, dan pisahkan hal-hal yang berubah karena alasan yang berbeda. *" Argumen ini menekankan konsep `cohesion` dengan sangat baik.

Microservice menerapkan konsep ini ke service yang independen, dan menentukan
batas-batas service berdasarkan batas-batas bisnis. Setiap service fokus pada
hal-hal di dalam batas service tersebut. Dengan melakukan hal ini, kita dapat
menghindari banyak masalah yang timbul akibat repository kode yang terlalu
besar.

Seberapa kecil seharusnya sebuah microservice? Cukup kecil, tetapi tidak
terlalu kecil.
Bagaimana cara mengevaluasi apakah suatu sistem sudah dipecah hingga cukup
kecil? Ketika Anda tidak memiliki keinginan lagi untuk membuatnya lebih kecil
di seluruh sistem, maka sistem tersebut sudah cukup kecil. Semakin kecil
service yang ada, semakin jelas pula kelebihan dan kekurangan dari
`Microservice`. Semakin kecil service yang digunakan, semakin besar manfaat
independensinya, tetapi pengelolaan sejumlah besar service juga akan menjadi
lebih rumit.

## Otonomi

Sebuah microservice adalah entitas independen, ia dapat di-deploy secara
independen, dan juga dapat berdiri sendiri sebagai sebuah proses operating
system. Terdapat isolasi antar-service, dan komunikasi antar-service dilakukan
melalui jaringan, sehingga memperkuat isolasi antar-service dan menghindari
coupling yang erat. Service harus dapat dimodifikasi secara independen, dan
deployment dari service tertentu seharusnya tidak menyebabkan perubahan pada
`Service Consumer`. Hal ini mengharuskan kita untuk mempertimbangkan seberapa
banyak bagian dari `Service Providers` yang harus diekspos dan apa yang harus
disembunyikan. Jika terlalu banyak yang diekspos, `Service Consumer` akan
terikat (coupled) dengan implementasi internal dari provider. Hal ini akan
membuat service secara langsung menghasilkan pekerjaan koordinasi tambahan,
sehingga mengurangi otonomi dari service tersebut.

## Manfaat Utama

### Heterogenitas teknologi

Dalam sistem yang memiliki beberapa service yang saling bekerja sama, teknologi
yang paling cocok untuk suatu service dapat dipilih secara berbeda di setiap
service. Karena service dipanggil melalui jaringan, realisasi service tidak akan
dibatasi oleh bahasa pemrograman atau framework sistem yang digunakan. Ini
berarti ketika sebagian sistem memerlukan peningkatan performa, implementasi
bagian tersebut dapat dibangun kembali menggunakan stack teknologi yang
berkinerja lebih baik.

### Elastisitas

Konsep kunci untuk mewujudkan sistem yang elastis adalah `Bulkhead`. Jika suatu
komponen atau service dalam sistem tidak tersedia, namun tidak menyebabkan
kegagalan beruntun (cascading failure), maka bagian sistem lainnya masih dapat
beroperasi dengan normal. Batas service (`service boundary`) dari microservice
jelas merupakan sebuah `Bulkhead`. Dalam sistem berarsitektur monolitik
(`Monolithic architecture`), yaitu sistem di bawah arsitektur `PHP-FPM`
tradisional, jika bagian tertentu tidak tersedia, maka dalam banyak kasus semua
fungsi menjadi tidak dapat digunakan. Meskipun sistem dapat di-deploy pada
beberapa node melalui teknologi seperti load balancing untuk mengurangi
probabilitas kegagalan sistem secara total, untuk sistem `Microservice`,
arsitektur itu sendiri dapat menangani ketidaktersediaan service dan masalah
seperti penurunan fungsi (functional degradation).

### Kemampuan ekspansi

Sistem berarsitektur monolitik (`monolithic architecture`) hanya dapat
diekspansi secara keseluruhan, bahkan jika hanya sebagian kecil dari sistem yang
memiliki masalah performa. Jika Anda menggunakan beberapa service yang lebih
kecil, Anda hanya perlu mengekspansi service yang memang perlu diekspansi,
sehingga service yang tidak perlu diekspansi dapat dijalankan di server yang
lebih murah dan menghemat biaya.

### Deployment yang sederhana

Dalam sistem berarsitektur monolitik (`monolithic architecture`) dengan jumlah
kode yang sangat besar, bahkan jika hanya satu baris kode yang dimodifikasi,
seluruh sistem harus di-deploy ulang untuk merilis perubahan tersebut.
Deployment semacam ini memiliki dampak yang besar dan risiko tinggi, sehingga
pihak terkait jarang melakukan deployment seperti itu. Oleh karena itu,
frekuensi deployment dalam operasional nyata menjadi sangat rendah. Banyak
fitur atau `Bugfix` akan ditambahkan ke sistem di antara versi-versi perilisan,
dan sejumlah besar perubahan akan dirilis ke lingkungan production sekaligus.
Namun, semakin besar perbedaan antara dua rilis, semakin besar pula
kemungkinan terjadinya kesalahan.
Tentu saja, dalam pengembangan di bawah arsitektur `PHP-FPM` tradisional, kita
mungkin tidak menghadapi masalah seperti itu, karena hot update sudah ada
secara alami. Namun, kelebihan dan kekurangan selalu ada secara bersamaan.

### Kesesuaian dengan struktur organisasi

Dalam kasus arsitektur monolitik (`Monolithic architecture`) dengan struktur
tim yang juga 'terdistribusi' (remote), konflik kode yang disebabkan oleh
penyerahan kode dari banyak engineer serta komunikasi iteratif di tempat yang
berbeda akan membuat pemeliharaan sistem menjadi lebih kompleks. Seperti yang
kita ketahui bahwa tim dengan ukuran yang tepat dapat memperoleh produktivitas
yang lebih tinggi dengan bekerja pada repository yang kecil, sehingga
pembagian service dapat membagi tanggung jawab terkait dengan baik.

### Komposabilitas

Manfaat utama yang ditawarkan oleh sistem terdistribusi (`Distributed System`)
dan `Service Oriented Architecture (SOA)` adalah kemudahan dalam menggunakan
kembali fungsi-fungsi yang sudah ada. Di bawah arsitektur `Microservice`,
pembagian service yang lebih terperinci (fine-grained) akan mencerminkan
keunggulan ini secara lebih nyata.

### Sangat mudah dikonfigurasi ulang

Jika Anda menghadapi sistem berarsitektur monolitik (`monolithic architecture`)
yang besar, kode di dalamnya berantakan, dan semua orang takut untuk melakukan
refactoring. Namun ketika Anda berurusan dengan service skala kecil yang
terperinci, melakukan refactoring pada suatu service atau bahkan menulis ulang
service tersebut relatif lebih mudah dilakukan.
Dalam sistem berarsitektur monolitik (`monolithic architecture`) yang besar,
dapatkah Anda yakin bahwa tidak akan ada masalah jika ratusan baris kode
dihapus dalam satu hari? Namun dengan `Microservice` yang baik, saya yakin
Anda dapat menghapus suatu service secara langsung tanpa masalah.

## Bukan Peluru Perak

Meskipun manfaat `Microservice` sangat banyak, namun, **Microservice bukanlah
peluru perak (silver bullet)! ! !**. Anda perlu mempertimbangkan kompleksitas
yang harus dihadapi oleh semua sistem terdistribusi. Anda mungkin perlu
melakukan banyak pekerjaan pada deployment, pengujian (testing), pemantauan
(monitoring), pemanggilan antar-service, dan keandalan service, bahkan Anda
harus menangani masalah seperti transaksi terdistribusi atau masalah terkait
CAP. Meskipun `Hyperf` telah menyelesaikan banyak masalah untuk Anda, tim Anda
harus memiliki pengetahuan yang cukup terkait dengan sistem terdistribusi
sebelum menerapkan `Microservice`, guna menghadapi masalah yang mungkin belum
pernah Anda hadapi atau pertimbangkan sebelumnya.

*| Sebagian konten dalam bab ini merujuk dari buku 《Building Microservices》 oleh Sam Newman*
