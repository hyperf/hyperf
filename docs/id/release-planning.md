# Perencanaan Rilis

## Siklus Hidup

| Versi | Status | Akhir dukungan utama | Akhir dukungan perbaikan keamanan | Tanggal Rilis (atau estimasi tanggal) |
|----------|------------------------|---------------------------|-------------------------------|----------------------------------|
| 3.1(LTS) | Mainstream support     | 2026-01-01                | 2027-01-01                    | 2023-12-01                       |
| 3.0      | Security fixes support | 2023-11-30                | 2024-06-30                    | 2023-01-03                       |
| 2.2      | Deprecated             | 2022-06-20                | 2023-11-30                    | 2021-07-19                       |
| 2.1      | Deprecated             | 2021-06-30                | 2021-12-31                    | 2020-12-28                       |
| 2.0      | Deprecated             | 2020-12-28                | 2021-06-30                    | 2020-06-22                       |
| 1.1      | Deprecated             | 2020-06-23                | 2020-12-31                    | 2019-10-08                       |
| 1.0      | Deprecated             | 2019-10-08                | 2019-12-31                    | 2019-06-20                       |

* Dukungan utama termasuk perbaikan BUG, perbaikan keamanan, pembaruan fungsi,
  dan dukungan fungsi baru dalam siklus iterasi reguler;
* Dukungan perbaikan keamanan hanya mencakup perbaikan masalah keamanan;
* Versi dengan status Deprecated tidak akan mengalami perubahan kode apa pun.
  Silakan tingkatkan ke versi terbaru sesuai dengan panduan peningkatan
  secepat mungkin untuk mendapatkan dukungan yang lebih baik;


## Siklus Iterasi Versi

Hyperf mengadopsi model pengembangan tangkas (agile), dengan rencana
peningkatan setiap minggu, dan merilis versi pada hari `Senin
(UTC/GMT+08:00)` setiap minggu, biasanya rilis versi z, atau versi y. Untuk
versi x, rencana iterasi dan waktu spesifik akan ditentukan berdasarkan hasil
riset yang sebenarnya.

Untuk aturan versi yang diadopsi oleh Hyperf, silakan merujuk ke bab
[Versi](id/versions.md).
