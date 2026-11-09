# Checklist Deployment

## Sudah disiapkan dalam branch deploy-vercel

- [x] Konfigurasi lokal MySQL dipertahankan; main tidak diubah.
- [x] Audit migration/query dan kompilasi grammar PostgreSQL.
- [x] SSL env dan opsi emulated prepares untuk pooler.
- [x] URL CMS dari Laravel Storage, termasuk hero/logo/favicon.
- [x] Driver media publik dan dokumen SPMB privat configurable ke S3.
- [x] Adapter S3 yang kompatibel ditambahkan tanpa major upgrade existing.
- [x] Migration sessions/cache/cache_locks.
- [x] Entry serverless, routing static/fallback, dan temporary filesystem.
- [x] Validasi token QR UUID, URL production, dan logo PDF dari disk.
- [x] Template env tanpa secret dan ignore deployment/private files.
- [x] Dokumentasi Supabase, Storage, Vercel, troubleshooting dan rollback.
- [x] Workflow PostgreSQL terisolasi disiapkan, belum dijalankan remote.

## Harus diselesaikan dengan akun/credential asli

- [ ] Buat project Supabase dan simpan credential SQL.
- [ ] Aktifkan pdo_pgsql pada CLI yang menjalankan migration.
- [ ] Buat bucket media public dan dokumen private.
- [ ] Buat S3 access key, secret, salin endpoint/region.
- [ ] Isi .env.deploy secara privat; pertahankan .env lokal.
- [ ] Buat/simpan APP_KEY production satu kali.
- [ ] Periksa koneksi/tujuan DB, migrate dan ProductionSeeder.
- [ ] Verifikasi exposure Data API/RLS untuk data sensitif.
- [ ] Import data resmi dan transfer file bila diperlukan; jangan hapus sumber.
- [ ] Review staged diff, commit dan push deploy-vercel; tidak merge main.
- [ ] Jalankan workflow PostgreSQL dan pastikan lulus.
- [ ] Import repository ke Vercel; production branch deploy-vercel.
- [ ] Isi seluruh Environment Variables yang asli dan APP_URL HTTPS.
- [ ] Runtime PHP/platform/extension check lulus pada Vercel.
- [ ] Build Vite dan static /build serta /images berfungsi.
- [ ] Login admin/pendaftar, session, CSRF, register, reset password.
- [ ] Hero, kontak, media sosial, kegiatan dan CMS lainnya.
- [ ] Upload media publik tetap tersedia setelah redeploy.
- [ ] Upload dokumen privat; akses tamu/akun lain ditolak.
- [ ] Alur SPMB, revisi, penerimaan/penolakan.
- [ ] QR mengarah ke domain production.
- [ ] PDF logo/QR dan export CSV berhasil dengan volume wajar.
- [ ] Uji batch upload di bawah batas payload Vercel.
- [ ] APP_DEBUG=false dan tidak ada secret/private file pada frontend.
- [ ] Simpan deployment rollback dan backup data; lakukan uji pemulihan.

Daftar yang masih kosong tidak boleh dianggap selesai hanya karena build lokal berhasil.
