# Diagnostik exception HTTP 500 Vercel

Tanggal: 15 November 2026. Branch deploy-vercel. Tidak melakukan commit, push, atau deploy.

## Audit

api/index.php sudah menjalankan HTTP Kernel Laravel. Kernel::handle() menangkap exception dari aplikasi, memanggil ExceptionHandler::report(), lalu melakukan render respons. Karena exception route tidak selalu keluar dari handle(), menambahkan try/catch di luar entrypoint saja tidak cukup. Entrypoint tidak diubah.

Handler existing meneruskan exception ke logger Laravel default, beserta pesan mentah, exception object, context dan stack trace. Laporan Runtime Logs yang mulai dari #38 belum cukup untuk membuktikan penyebab hilangnya baris awal atau penyebab HTTP 500. Diagnostik baru membuat ringkasan utama yang terpisah, ringkas dan satu baris, tanpa bergantung pada tampilan stack trace panjang.

## Implementasi

- Pada app.vercel=true, Handler melaporkan exception yang memang perlu dilaporkan melalui SafeExceptionDiagnostic dan error_log.
- Rantai previous ditelusuri maksimal delapan lapis agar penyebab utama tidak tertutup QueryException/ViewException pembungkus. Jika berbeda, wrapper_class juga dicatat. Rantai lebih dalam dari delapan lapis dapat melaporkan exception perantara.
- Field: exception_class, message tersanitasi, message_redacted, file relatif, line, request_path berupa template route. Tidak mencatat query string, URL lengkap, nilai parameter, body, header, cookie, IP, user ID, session atau trace arguments.
- Pesan database hanya mencatat SQLSTATE dan kategori error. SQL, bindings, detail koneksi, nama/value dari query tidak dicatat. Pesan teknis lain direkonstruksi dari pola yang dikenali; nama/identifier/path dalam pesan dihilangkan. Pesan bebas yang tidak dikenali diganti dengan keterangan withheld for privacy. Ini sengaja tidak mencetak getMessage() mentah.
- Nilai environment dan secret konfigurasi digunakan hanya untuk masking tambahan, tidak dicetak atau didump. Tidak mengubah environment atau konfigurasi.
- Nama file log dinormalisasi relatif terhadap project; direktori compiled view/temp tidak mencetak prefix path personal. JSON meng-escape karakter kontrol dan log metadata dibatasi panjangnya.
- Exception mentah tidak diteruskan ke Monolog pada Vercel agar credential/SQL/PII tidak bocor melalui log duplikat. Ini penggantian mekanisme pelaporan exception khusus Vercel, bukan penekanan respons exception. Tidak ada perubahan konfigurasi logging.php atau channel lainnya.
- Pada non-Vercel, parent::report() tetap dipakai tanpa perubahan.
- Error validasi/auth yang tidak dilaporkan Laravel tetap mengikuti kebijakan tersebut. HttpException dengan status >=500 dicatat. Render, redirect 422 existing dan status HTTP tidak diubah.
- Jika penulisan diagnostik gagal, kegagalan logging tidak mengganti exception yang sedang dirender. Tidak ada fallback HTTP 200.

Cari marker berikut di Vercel Runtime Logs setelah pengguna menyetujui deployment nanti:

```text
[laravel.exception]
```

Contoh ilustratif, bukan error production yang sudah terkonfirmasi:

```json
{"exception_class":"PDOException","message":"SQLSTATE[42P01]: relation does not exist (SQL and values omitted)","message_redacted":true,"file":"vendor/laravel/framework/src/Illuminate/Database/Connection.php","line":123,"request_path":"/","wrapper_class":"Illuminate\\Database\\QueryException"}
```

Untuk route berparameter, path berbentuk /dashboard/{application}; nilai parameter/token tidak dicatat. Jika belum ada route cocok, path tidak dikenal menjadi [unmatched route], bukan raw user path. Untuk pesan yang disamarkan, gunakan class/file/line/route untuk menelusuri source; jangan mengaktifkan APP_DEBUG atau mencetak pesan mentah berisi data produksi.

## Pengujian

- Reproduksi sebelum perubahan: tiga kasus tidak mempunyai baris error_log diagnostik.
- Sesudah perubahan: 10 tests, 49 assertions lulus.
- PHPUnit keseluruhan: 123 tests, 996 assertions lulus.
- Syntax Handler.php dan SafeExceptionDiagnostic.php: lulus.
- Diff whitespace: bersih.

Kasus yang diuji: HTTP 500 tetap 500; root PDOException di balik QueryException; SQL/bindings/password/email disembunyikan; APP_KEY/AWS/token/name/phone/newline injection tidak tercetak; parameter/query/bearer token tidak tercetak; path tanpa route disamarkan; secret konfigurasi tidak muncul kembali pada pesan teknis; log ringkas satu kali; exception mentah tidak diteruskan ke logger default pada Vercel; respons sukses tidak berubah dan tidak menghasilkan diagnostik; validation exception tetap tidak dilaporkan; pelaporan non-Vercel tetap Laravel default; abort(500) tetap 500 dan tercatat.

Pengujian menggunakan exception/route sintetis, tidak mengambil atau mencetak credential produksi, serta tidak menjalankan request production. Regresi penuh memakai fixture SQLite terisolasi; tidak memutasi Supabase. Temporary directory proses tes di storage/app/testing karena drive sistem sebelumnya penuh.

```powershell
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit tests/Feature/SafeExceptionDiagnosticTest.php
php -d sys_temp_dir=storage/app/testing/pdf-logo-audit/temp vendor/phpunit/phpunit/phpunit
```

Review independen memeriksa source tiga file kode/tes dan tidak menemukan masalah penting; reviewer tidak menjalankan tes atau mengakses env/layanan. Batas kedalaman delapan wrapper dicatat sebagai keterbatasan kecil.

## File perubahan

1. app/Exceptions/Handler.php — jalur report khusus Vercel, render tetap existing.
2. app/Support/SafeExceptionDiagnostic.php — normalisasi dan error_log satu baris aman.
3. tests/Feature/SafeExceptionDiagnosticTest.php — pengujian status, privasi, keamanan dan jalur lokal.
4. docs/VERCEL_EXCEPTION_DIAGNOSTICS_2026-11-15.md — laporan ini.

api/index.php, vercel.json, config, routes, dependency, APP_KEY, env files, database, Storage, autentikasi, SPMB, PDF dan QR tidak diubah.

## Batas hasil

Ini perbaikan diagnostik, bukan klaim akar HTTP 500 production sudah ditemukan atau diperbaiki. Diagnostik baru belum berjalan di Vercel karena belum ada commit/push/deployment. Exception yang terjadi sebelum bootstrap/penyiapan Handler tidak tercakup oleh hook ini; task sekarang menargetkan exception aplikasi pada request yang sudah masuk Laravel (login 200). Access log atau log PHP/runtime upstream di luar Handler tidak diubah oleh pekerjaan ini; jaminan sanitasi di atas berlaku untuk diagnostik yang ditambahkan dan jalur report exception Vercel ini.
## Audit aktivasi VERCEL — 15 November 2026

config/app.php sudah membaca 'vercel' => env('VERCEL', false). Handler memakai config app.vercel saat konfigurasi tersedia; jalur non-Vercel memanggil parent::report(). Tidak dilakukan perubahan Handler/config production dalam audit aktivasi ini.

Dokumentasi resmi Vercel menyatakan indikator VERCEL=1 tersedia pada build dan runtime ketika system environment variables diekspos: https://vercel.com/docs/environment-variables/system-environment-variables#vercel. Audit ini tidak membaca/mencetak environment runtime production secara langsung dan tidak mengubah pengaturan exposure di dashboard.

Pengaman project existing: api/index.php memanggil VercelRuntime::prepare() sebelum bootstrap aplikasi dan pemuatan konfigurasi. Method tersebut menetapkan VERCEL=1 pada process environment, $_ENV dan $_SERVER, sehingga deteksi project tidak bergantung hanya pada injection system variable platform. Tidak perlu menambahkan flag manual atau mengubah konfigurasi production untuk jalur entrypoint tersebut. Handler juga aktif pada deployment Preview Vercel; production termasuk dalam cakupan tersebut.

Tes baru membaca config/app.php yang sebenarnya dari proses PHP terisolasi lalu menjalankan Handler, bukan sekadar memaksa config app.vercel pada aplikasi tes:

| Input pada child process | config aktif | Safe diagnostic | Logger Laravel biasa |
|---|---|---|---|
| true | ya | ya | tidak dipanggil |
| 1 | ya | ya | tidak dipanggil |
| false | tidak | tidak | dipanggil sekali |
| 0 | tidak | tidak | dipanggil sekali |
| kosong | tidak | tidak | dipanggil sekali |
| tidak ada | tidak | tidak | dipanggil sekali |
| tidak ada, lalu Runtime::prepare() | ya | ya | tidak dipanggil |

Gabungan VercelDiagnosticFlagTest dan SafeExceptionDiagnosticTest: **17 tests, 77 assertions lulus**. Proses tes hanya mengembalikan boolean dan jumlah pemanggilan logger. Tidak mencetak APP_KEY, password, secret AWS, atau env dump. Tidak memuat HTTP kernel, membuka DB, atau mengakses Supabase. Perubahan environment hanya terjadi dalam child process tes, bukan file .env atau konfigurasi deployment.

File tambahan audit ini:
- tests/Unit/VercelDiagnosticFlagTest.php
- tests/Support/vercel-diagnostic-flag.php
- Catatan pada dokumen diagnostik ini.

Batas: Handler mengikuti config app.vercel yang sudah dimuat. Mengubah process environment setelah konfigurasi dibaca tidak otomatis mengubah config tersebut. Urutan prepare sebelum bootstrap pada entrypoint existing sudah benar. Tidak ada commit, push atau deploy; aktivasi diagnostik pada deployment production yang baru tetap perlu dilihat dari Runtime Logs setelah pengguna menyetujui deployment kode diagnostiknya.