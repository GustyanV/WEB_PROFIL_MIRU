# Audit dependency keamanan — 19 Oktober 2026

**24 entri advisory sebelum dan sesudah; tidak ada advisory yang hilang.** Update minor wrapper berhasil, tetapi proyek belum bebas advisory. Tes fungsional yang lulus tidak membuktikan kerentanan telah diperbaiki.

## Lingkungan dan backup

- PHP aktual 8.0.30; requirement tetap ^8.0.2. Laravel 9.52.22 sudah versi terakhir seri 9 menurut metadata Composer saat audit.
- Backup: storage/app/security-upgrade/20261019-100941/ berisi composer.json, composer.lock, status Git dan diff sebelum perubahan.
- Working tree awal berisi perubahan CMS belum commit; semuanya dipertahankan.
- Tidak ada upgrade major/PHP, override platform, alias versi, audit ignore, perubahan flow/desain, atau perubahan database existing. Tes baru memakai SQLite in-memory; migrate:fresh/db:wipe tidak dijalankan.

## Package yang diupdate

| Package | Sebelum | Sesudah | Jenis |
| --- | --- | --- | --- |
| barryvdh/laravel-dompdf | v2.0.0 | v2.2.0 | direct/runtime |

Constraint wrapper berubah dari 2.0 ke ^2.2. Dompdf tetap 2.0.8. Wrapper ini masih menerima Dompdf ^2.0.7, sehingga update minor tersebut **tidak memperbaiki enam advisory Dompdf**.

## Klasifikasi semua package terdampak

| Package | Jenis | Lingkup | Versi sebelum → sesudah | Entri | Severity | Target perbaikan relevan |
| --- | --- | --- | --- | ---: | --- | --- |
| dompdf/dompdf | transitive | runtime | v2.0.8 → v2.0.8 | 6 | {"medium":4,"low":2} | 3.1.6; wrapper 3.x memerlukan PHP >=8.1 |
| laravel/framework | direct | runtime | v9.52.22 → v9.52.22 | 5 | {"low":1,"medium":2,"high":1,"unspecified":1} | 12.69.0 untuk seluruh temuan pada cabang 12; PHP >=8.2 |
| symfony/http-foundation | transitive | runtime | v6.0.20 → v6.0.20 | 3 | {"unspecified":1,"high":1,"low":1} | 6.4.46; PHP >=8.1 |
| symfony/mailer | transitive | runtime | v6.0.19 → v6.0.19 | 1 | {"medium":1} | 6.4.40; PHP >=8.1 |
| symfony/mime | transitive | runtime | v6.0.19 → v6.0.19 | 2 | {"medium":1,"high":1} | 6.4.40; PHP >=8.1 |
| symfony/process | transitive | runtime | v6.0.19 → v6.0.19 | 1 | {"high":1} | 6.4.14; PHP >=8.1 |
| symfony/routing | transitive | runtime | v6.0.19 → v6.0.19 | 2 | {"medium":2} | 6.4.41; PHP >=8.1 |
| symfony/yaml | transitive | development | v6.0.19 → v6.0.19 | 4 | {"unspecified":1,"low":3} | 6.4.45; PHP >=8.1 |

Severity: **4 high, 10 medium, 7 low, 3 tidak dicantumkan sumber**. Severity kosong tidak dianggap low. Ada 24 entri dan 23 advisory unik: dua entri Laravel CRLF merujuk GHSA yang sama dari sumber berbeda. Wrapper tidak memiliki advisory tersendiri. Symfony YAML berada pada dependency development.

## Daftar seluruh advisory tersisa

Versi Symfony di bawah adalah versi cabang 6.4 yang relevan dengan constraint Laravel 9, bukan pin dependency baru. Rentang affectedVersions dan metadata asli disimpan utuh di [before.json](security-audit/before.json) dan [after.json](security-audit/after.json).

| Package | Advisory / CVE | Severity | Temuan | Versi perbaikan |
| --- | --- | --- | --- | --- |
| dompdf/dompdf | [PKSA-cv56-2228-pzr6](https://github.com/advisories/GHSA-j8qw-6jw8-r297) / CVE-2026-59943 | medium | Dompdf: Embedded SVG images can leak existence of files and directories within the filesystem | 3.1.6 |
| dompdf/dompdf | [PKSA-6r8f-nxsb-67bq](https://github.com/advisories/GHSA-f5gf-2cj8-52g2) / CVE-2026-59942 | medium | Dompdf: Denial of Service (DoS) via Resource Exhaustion using Oversized Image Bitmaps | 3.1.6 |
| dompdf/dompdf | [PKSA-gh7h-hhy4-byg7](https://github.com/advisories/GHSA-8hg6-c449-896m) / CVE-2026-59941 | medium | Dompdf: Uncontrolled resource consumption based on declared BMP dimensions | 3.1.6 |
| dompdf/dompdf | [PKSA-mwt3-h9tv-kx78](https://github.com/advisories/GHSA-cx96-42px-69fm) / CVE-2026-56722 | medium | Dompdf: Local file read due to improper file path validation in SVG images encoded as data-URI | 3.1.6 |
| dompdf/dompdf | [PKSA-hp6n-n4kz-21wk](https://github.com/advisories/GHSA-7x2p-4jvh-6384) / CVE-2026-55555 | low | Dompdf: File existence oracle via font-face stylesheet declaration | 3.1.6 |
| dompdf/dompdf | [PKSA-mckv-s5hg-868k](https://github.com/advisories/GHSA-wvh6-f5jh-8gw4) / CVE-2026-55554 | low | Dompdf: Chroot Validation Bypass | 3.1.6 |
| laravel/framework | [PKSA-d5tc-s1qs-h781](https://github.com/advisories/GHSA-jh5r-qr3c-85q8) / CVE-2026-102279 | low | Laravel: XSS in Debug Page Information | 12.69.0 / 13.30.0 |
| laravel/framework | [PKSA-m5cs-t1y6-qpcs](https://github.com/advisories/GHSA-crmm-hgp2-wgrp) | medium | Laravel Framework: Temporary Signed URL Path Confusion | 12.61.1 / 13.12.0 |
| laravel/framework | [PKSA-3r5d-mb8f-1qw9](https://github.com/advisories/GHSA-5vg9-5847-vvmq) | high | Laravel Framework: CRLF injection in default email rule  | 12.60.0 / 13.10.0 |
| laravel/framework | [PKSA-mdq4-51ck-6kdq](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) / CVE-2026-48019 | Tidak dicantumkan | Laravel CRLF injection in default email rule | 12.60.0 / 13.10.0 |
| laravel/framework | [PKSA-8qx3-n5y5-vvnd](https://github.com/advisories/GHSA-78fx-h6xr-vch4) / CVE-2025-27515 | medium | Laravel has a File Validation Bypass | 10.48.29 / 11.44.1 / 12.1.1 |
| symfony/http-foundation | [PKSA-xmwy-rmx5-m7h4](https://symfony.com/cve-2026-108082) / CVE-2026-108082 | Tidak dicantumkan | CVE-2026-108082: ReDoS in HeaderUtils::split() Reachable Through the Cache-Control Request Header | 6.4.46 |
| symfony/http-foundation | [PKSA-365x-2zjk-pt47](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) / CVE-2025-64500 | high | CVE-2025-64500: Incorrect parsing of PATH_INFO can lead to limited authorization bypass | 6.4.29 |
| symfony/http-foundation | [PKSA-b35n-565h-rs4q](https://symfony.com/cve-2024-50345) / CVE-2024-50345 | low | CVE-2024-50345: Open redirect via browser-sanitized URLs | 6.4.14 |
| symfony/mailer | [PKSA-28rh-rzzn-djk4](https://symfony.com/cve-2026-45068) / CVE-2026-45068 | medium | CVE-2026-45068: Argument Injection in SendmailTransport via Dash-Prefixed Recipient Address | 6.4.40 |
| symfony/mime | [PKSA-wtxr-p26d-nn42](https://symfony.com/cve-2026-45070) / CVE-2026-45070 | medium | CVE-2026-45070: Email Header Injection via Non-Token Characters in Mime Parameter Names | 6.4.40 |
| symfony/mime | [PKSA-2n2k-66v2-bwg3](https://symfony.com/cve-2026-45067) / CVE-2026-45067 | high | CVE-2026-45067: Email Header / SMTP Command Injection via CRLF in Symfony\Component\Mime\Address | 6.4.40 |
| symfony/process | [PKSA-wws7-mr54-jsny](https://symfony.com/cve-2024-51736) / CVE-2024-51736 | high | CVE-2024-51736: Command execution hijack on Windows with Process class | 6.4.14 |
| symfony/routing | [PKSA-bf7t-jnpz-492k](https://symfony.com/cve-2026-48784) / CVE-2026-48784 | medium | CVE-2026-48784: UrlGenerator Dot-Segment Encoding Skips Every Other Chained `../` or `./` → Generated URL Collapses Off-Route Under RFC 3986 Normalization | 6.4.41 |
| symfony/routing | [PKSA-yc7t-91v9-99xs](https://symfony.com/cve-2026-45065) / CVE-2026-45065 | medium | CVE-2026-45065: UrlGenerator Route-Requirement Bypass via Unanchored Regex Alternation → Off-Site //host URL Injection | 6.4.40 |
| symfony/yaml | [PKSA-xz3r-9gp4-4m1z](https://symfony.com/cve-2026-109413) / CVE-2026-109413 | Tidak dicantumkan | CVE-2026-109413: Unbounded Recursion in the YAML Inline Lexer Lets a Single Document Crash the Process | 6.4.45 |
| symfony/yaml | [PKSA-v5yj-8nmz-sk2q](https://symfony.com/cve-2026-45304) / CVE-2026-45304 | low | CVE-2026-45304: YAML Parser Exponential Memory Allocation via Recursive Collection-Alias Expansion ("Billion Laughs") | 6.4.40 |
| symfony/yaml | [PKSA-ft77-7h5f-p3r6](https://symfony.com/cve-2026-45305) / CVE-2026-45305 | low | CVE-2026-45305: YAML Parser ReDoS via Catastrophic Backtracking in Parser::cleanup() Regex | 6.4.40 |
| symfony/yaml | [PKSA-b14r-zh1d-vdrc](https://symfony.com/cve-2026-45133) / CVE-2026-45133 | low | CVE-2026-45133: YAML Parser Stack Exhaustion via Unbounded Recursion in Nested Blocks, Sequences, and Mappings | 6.4.40 |

## Alasan belum diperbaiki, risiko, dan tahap berikutnya

- **Laravel:** tidak ada patch seri 9 untuk temuan ini. Seluruh temuan saat audit membutuhkan sekurangnya Laravel 12.69.0 pada cabang 12, dengan PHP >=8.2. Risiko: bypass validasi file, injeksi CRLF email, kebingungan signed URL, dan XSS debug page.
- **Dompdf:** versi aman minimum 3.1.6 tidak diizinkan wrapper 2.x. Wrapper 3.1.2 mendukung Laravel 9 tetapi membutuhkan PHP ^8.1. Risiko: pembacaan file lokal, kebocoran keberadaan file, bypass chroot, dan DoS dari gambar/SVG/font berbahaya. Template server dan escaping teks aplikasi membatasi beberapa jalur eksploitasi, tetapi bukan perbaikan advisory.
- **HttpFoundation:** patch 6.4 membutuhkan PHP >=8.1; risiko ReDoS header, bypass otorisasi akibat parsing path, dan open redirect.
- **Mailer/Mime:** patch 6.4 membutuhkan PHP >=8.1; risiko injeksi alamat/header/SMTP dan argumen sendmail sesuai transport yang digunakan.
- **Process:** patch 6.4 membutuhkan PHP >=8.1; risiko pembajakan eksekusi command di Windows pada kondisi yang terdampak.
- **Routing:** patch 6.4 membutuhkan PHP >=8.1; risiko URL off-route dan off-site pada route tertentu.
- **YAML:** patch 6.4 membutuhkan PHP >=8.1; risiko kehabisan stack/memori dan ReDoS jika parser menerima input YAML berbahaya; saat ini dependency development.

Rekomendasi tahap terpisah: siapkan PHP yang masih didukung, minimal 8.2 untuk target Laravel 12; lakukan migrasi Laravel bertahap dan teruji menuju >=12.69.0 atau rilis supported yang lebih baru. Pasang wrapper 3.x dengan Dompdf >=3.1.6 dan biarkan Composer memilih Symfony patched yang kompatibel. Jalankan ulang audit pada saat pelaksanaan. Upgrade major/PHP tidak dilakukan otomatis.

## Perintah yang dijalankan

1. composer audit --format=json
2. composer prohibits symfony/http-foundation 6.4.46 --tree
3. composer prohibits barryvdh/laravel-dompdf 3.1.2 --tree
4. composer prohibits dompdf/dompdf 3.1.6 --tree
5. composer require barryvdh/laravel-dompdf:^2.2 --dry-run --no-scripts --no-interaction
6. composer update barryvdh/laravel-dompdf --with-dependencies --no-interaction
7. composer update laravel/framework --with-dependencies --dry-run --no-scripts --no-interaction
8. composer update symfony/http-foundation symfony/mailer symfony/mime symfony/process symfony/routing symfony/yaml --with-dependencies --dry-run --no-scripts --no-interaction

Require hanya dry-run; constraint aktual ditulis menjadi ^2.2 sebelum update terarah. Dry-run Laravel hanya menawarkan CommonMark 2.10.4, bukan Laravel yang lebih baru; CommonMark tidak diupdate karena bukan perbaikan advisory pada audit ini. Dry-run Symfony: Nothing to modify in lock file. Tidak ada composer update penuh tanpa target.

## Verifikasi setelah update

| Pemeriksaan | Hasil |
| --- | --- |
| optimize:clear | Berhasil |
| migrate:status | Seluruh migration Ran; database existing tidak diubah |
| route:list | Berhasil, 104 route |
| php artisan test | 35 lulus: 34 sebelumnya + tes PDF panjang |
| npm run build | Berhasil; warning existing hero-pattern.png dimuat runtime |
| check-platform-reqs | Seluruh requirement terpenuhi pada PHP 8.0.30 |
| composer validate | Valid; warning existing exact constraint simple-qrcode 4.2 |
| audit akhir | 24 entri / 8 package; tidak bersih, 0 advisory hilang |

Regresi: login/role, submit, revision, verified, accepted/rejected, PDF pendaftar/admin, QR/verifikasi token, CSV, seluruh CMS, akun admin, periode dan gelombang. Tes PDF tambahan memeriksa binary PDF, A4 portrait, embedded image/logo, data QR SVG, dan data panjang. Tidak ditemukan breaking change pada jalur yang diuji; perbandingan visual pixel-per-pixel PDF tidak dilakukan. Engine Dompdf tidak berubah.

Saat regenerasi manifest Composer, log lokal mencatat konflik sementara Windows rename/access denied pada bootstrap/cache/services.php dari proses browser/LSP paralel. Temuan lingkungan ini tidak disembunyikan. Manifest akan diregenerasi dan dicek ulang; hasil akhirnya dicatat pada catatan penutup.

## Sumber primer

- [Wrapper 2.2 requirements](https://raw.githubusercontent.com/barryvdh/laravel-dompdf/v2.2.0/composer.json)
- [Wrapper 3.1.2 requirements](https://raw.githubusercontent.com/barryvdh/laravel-dompdf/v3.1.2/composer.json)
- [Dompdf patched 3.1.6 advisory](https://github.com/dompdf/dompdf/security/advisories/GHSA-cx96-42px-69fm)
- [Symfony HttpFoundation 6.4.46 requirement](https://raw.githubusercontent.com/symfony/http-foundation/v6.4.46/composer.json)
- [Laravel 12.69.0 requirement](https://raw.githubusercontent.com/laravel/framework/v12.69.0/composer.json)

## Catatan akhir manifest dan log

Manifest package berhasil diregenerasi ulang melalui package:discover. Probe HTTP lokal GET untuk /, /login, /kegiatan, /kontak menghasilkan 200; /admin tanpa login menghasilkan redirect 302. Probe hanya membaca data existing. Tidak ada penambahan log pada probe setelah pemulihan manifest. Kegagalan sementara file-lock Windows saat regenerasi sebelumnya tetap dicatat pada log, tidak dihapus.