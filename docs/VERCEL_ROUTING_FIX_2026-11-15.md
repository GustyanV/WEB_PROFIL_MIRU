# Perbaikan routing Vercel ? 15 November 2026

Baseline commit 4c7feb4, branch deploy-vercel. Tidak ada push atau deployment.

## Penyebab yang terbukti

Audit production sebelumnya menemukan GET / mengembalikan HTTP 200, Content-Type application/x-httpd-php, Content-Disposition inline, tanpa redirect, dan body 1.710 byte identik dengan public/index.php. GET /login menghasilkan HTML Laravel. Root mengambil index statis dari outputDirectory public pada fase filesystem sebelum catch-all. Aturan URL .php tidak melindungi request /.

## vercel.json sebelum

```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "version": 2,
  "framework": null,
  "installCommand": "npm ci",
  "buildCommand": "npm run build",
  "outputDirectory": "public",
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.6.2",
      "maxDuration": 60
    }
  },
  "routes": [
    {
      "src": "/storage(?:/.*)?",
      "status": 404
    },
    {
      "src": "/(?:\\.env[^/]*|\\.git)(?:/.*)?",
      "status": 404
    },
    {
      "src": "/.*\\.php",
      "dest": "/api/index.php"
    },
    {
      "handle": "filesystem"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
```

## vercel.json sesudah

```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "version": 2,
  "framework": null,
  "installCommand": "npm ci",
  "buildCommand": "npm run build",
  "outputDirectory": "public",
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.6.2",
      "maxDuration": 60
    }
  },
  "routes": [
    {
      "src": "/storage(?:/.*)?",
      "status": 404
    },
    {
      "src": "/(?:.*/)?\\.[^/]*(?:/.*)?",
      "status": 404
    },
    {
      "src": "/.*\\.(?:[pP][hH][pP][0-9]?|[pP][hH][tT][mM][lL]|[pP][hH][aA][rR]|[iI][nN][cC])(?:[./].*)?",
      "status": 404
    },
    {
      "src": "/",
      "dest": "/api/index.php"
    },
    {
      "src": "/((?:build|images)/.*\\.(?:css|js|mjs|png|jpg|jpeg|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|eot)|build/manifest\\.json)",
      "dest": "/$1"
    },
    {
      "src": "/([^/]+\\.(?:css|js|mjs|ico|png|jpg|jpeg|gif|webp|avif|svg|txt|xml|webmanifest|woff|woff2|ttf|otf|pdf))",
      "dest": "/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
```

## Alasan perubahan

- outputDirectory tetap public, sehingga repository Laravel tidak dijadikan root aset. Runtime vercel-php@0.6.2, maxDuration, installCommand dan buildCommand tetap.
- Blok /storage tetap berada di awal.
- Blok dotfile diperluas ke semua segmen path, termasuk .env dan .git di dalam direktori aset.
- Akses publik PHP/PHP-number/PHTML/PHAR/INC ditolak sebelum aturan aset, termasuk uppercase, double-extension file.php.jpg dan path-info index.php/extra.
- Root / mempunyai aturan eksplisit menuju /api/index.php.
- Handle filesystem dihapus sepenuhnya. Tidak ada lagi pemilihan default index statis oleh aturan ini.
- Build/images dipetakan sebagai aset hanya untuk ekstensi frontend/gambar/font yang diizinkan. build/manifest.json diizinkan secara spesifik; JSON arbitrary tidak dipublikasikan.
- File root publik dengan ekstensi aman (favicon, robots.txt, sitemap.xml, webmanifest, gambar/font/CSS/JS/PDF) tetap dapat disajikan. File aset yang tidak ada menghasilkan 404, bukan index direktori.
- Catch-all terakhir menuju function /api/index.php untuk route Laravel lainnya.
- .vercelignore mengecualikan public/index.php, pola public PHP uppercase/double-extension, PHTML/PHAR dan .htaccess dari input deployment. File index lokal tetap ada dalam repository. api/index.php tidak dikecualikan.

Akses browser langsung /api/index.php ditolak. Destination internal root/catch-all tetap memilih function tersebut; destination low-level bukan request publik baru yang mengulangi aturan deny. Ini tetap memerlukan smoke test pada edge Vercel setelah deployment yang nanti diizinkan.

## api/index.php

Tidak diubah. Entrypoint telah memuat autoloader, storage sementara serverless, bootstrap aplikasi dan HTTP Kernel handle/send/terminate. Hot-file Vite development juga sudah dinonaktifkan. Hasil HTTP lokal membuktikan entrypoint menghasilkan HTML saat root/login benar-benar diarahkan kepadanya.

## File yang berubah

1. vercel.json ? routing aman dan root eksplisit.
2. .vercelignore ? pengecualian public PHP dari deployment.
3. tests/Unit/VercelRoutingTest.php ? 35 tes/kasus kebijakan route dan pengecualian artefak.
4. tests/Feature/DeploymentCompatibilityTest.php ? mengganti assertion filesystem lama.
5. docs/VERCEL_ROUTING_FIX_2026-11-15.md ? laporan ini dengan konfigurasi lengkap sebelum/sesudah.

Tidak ada perubahan APP_KEY, environment, database Supabase, Storage, autentikasi, SPMB, PDF, QR, dependency atau versi runtime.

## Hasil verifikasi

- Sebelum perbaikan, tes mereproduksi root -> static /index.php dan gagal pada kebijakan yang diperlukan.
- Sesudah perbaikan: VercelRoutingTest 35 tests, 40 assertions lulus.
- Routing + DeploymentCompatibilityTest: 43 tests, 90 assertions lulus.
- JSON valid melalui JSON_THROW_ON_ERROR dan JSON.parse; regex valid dan keputusan pencocokan route diuji.
- Pola ignore diverifikasi dengan picomatch existing: public/index.php, nested uppercase PHP, double-extension, PHTML/PHAR dikecualikan; function api/index.php, Vite JS dan logo PNG dipertahankan.
- php -l api/index.php: tidak ada kesalahan sintaks.
- Diff whitespace bersih.

HTTP lokal memakai aturan konfigurasi baru dan entrypoint Laravel asli:

| Request | Status | Content-Type | Content-Disposition |
|---|---:|---|---|
| / | 200 | text/html; charset=UTF-8 | tidak ada |
| /login | 200 | text/html; charset=UTF-8 | tidak ada |
| /build/assets/app-24f3b7c0.css | 200 | text/css; charset=UTF-8 | tidak ada |
| /build/assets/app-746b447b.js | 200 | application/javascript | tidak ada |
| /images/logo-miru.png | 200 | image/png | tidak ada |
| /robots.txt | 200 | text/plain; charset=UTF-8 | tidak ada |
| /index.php | 404 | text/plain | tidak ada |
| /api/index.php | 404 | text/plain | tidak ada |
| /images/fake.php.jpg | 404 | text/plain | tidak ada |
| /.env | 404 | text/plain | tidak ada |
| /.git/config | 404 | text/plain | tidak ada |
| /storage/private.txt | 404 | text/plain | tidak ada |

Body root/login terverifikasi HTML, bukan source PHP, dan tidak menjadi attachment. Harness menggunakan SQLite fixture existing serta session/cache array dan storage lokal untuk proses tes; tidak mengakses atau memutasi Supabase dan tidak mengubah file .env. Artefak QA berada di storage/app/testing/vercel-routing-http-report.json, vercel-routing-router.php dan vercel-routing-http.cjs (ignored).

Tes ini memverifikasi konfigurasi serta perilaku lokal, bukan eksekusi edge Vercel sebenarnya. Production belum diperbaiki karena belum ada push/deployment. Error 500 lama pada akses PHP langsung tidak diperbaiki di Laravel; URL itu sekarang dirancang ditolak 404 oleh routing publik. Penambahan tipe file statis atau route dinamis berakhiran ekstensi aset di masa depan perlu mengevaluasi allowlist ini.

## Pemeriksaan setelah deployment nanti

GET/HEAD root dan login harus menghasilkan HTML, bukan application/x-httpd-php, tanpa attachment. CSS/JS/logo tetap 200; PHP/dotfile/storage ditolak. Pastikan dashboard Files/Functions menampilkan api/index.php sebagai function dan public/index.php tidak sebagai aset statis. Tidak memerlukan perubahan environment/database untuk routing ini.

Referensi: https://vercel.com/docs/project-configuration/vercel-json dan README artefak runtime vercel-php 0.6.2 yang sudah diperiksa. Tidak ada upgrade dependency, commit, push atau deploy.
