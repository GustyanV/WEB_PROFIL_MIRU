<div class="md:col-span-2 rounded-xl border p-4">
    <h2 class="text-lg font-bold">Logo untuk bukti PDF</h2>
    <p class="mt-2 text-sm">Logo website tetap memakai file asli, termasuk transparansinya. Salinan PDF dibuat menjadi JPEG berlatar putih tanpa mengubah dimensi. Periksa ketajaman dan warna sebelum menyimpan.</p>
    <button type="button" data-prepare-existing-logo class="btn-secondary mt-3">SIAPKAN JPEG PDF LOGO SAAT INI</button>
    <input type="file" name="pdf_logo" accept="image/jpeg" hidden>
    <input type="hidden" name="pdf_logo_hash">
    <img data-pdf-logo-preview hidden alt="Preview JPEG logo PDF dengan latar putih" class="mt-4 max-h-64 max-w-full object-contain border bg-white">
    <p data-pdf-logo-message role="status" aria-live="polite" class="mt-2 text-sm text-slate-600">Untuk logo lama PNG/WebP, klik tombol di atas lalu simpan. Logo asli tidak dihapus.</p>
    <label class="mt-3 flex items-center gap-2"><input type="checkbox" name="pdf_logo_approved" value="1">Saya sudah memeriksa preview; kualitas dan warna logo PDF sesuai.</label>
    @error('pdf_logo')<p class="text-red-700">{{ $message }}</p>@enderror
    @error('pdf_logo_approved')<p class="text-red-700">{{ $message }}</p>@enderror
</div>
