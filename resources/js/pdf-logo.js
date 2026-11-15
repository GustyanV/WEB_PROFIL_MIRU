// Browser-only conversion. Original files are never replaced or resized.
export async function convertPdfLogo(blob) {
    const image = await createImageBitmap(blob);
    try {
        if (image.width * image.height > 20000000) throw new Error('Logo maksimal 20 juta piksel. Gunakan logo dengan dimensi lebih kecil.');
        const canvas = document.createElement('canvas');
        canvas.width = image.width; canvas.height = image.height;
        const context = canvas.getContext('2d');
        if (!context) throw new Error('Browser tidak mendukung konversi logo.');
        context.fillStyle = '#ffffff'; context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0);
        // JPEG input can be used verbatim; other formats get full-quality JPEG.
        const jpeg = blob.type === 'image/jpeg' ? blob : await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 1));
        if (!jpeg || jpeg.type !== 'image/jpeg') throw new Error('Konversi JPEG gagal.');
        if (jpeg.size > 2097152) throw new Error('JPEG PDF melebihi 2 MB. Konversi dihentikan tanpa menurunkan kualitas atau mengubah logo asli.');
        const digest = await crypto.subtle.digest('SHA-256', await blob.arrayBuffer());
        return { file: new File([jpeg], 'logo-pdf.jpg', { type: 'image/jpeg' }), hash: Array.from(new Uint8Array(digest), b => b.toString(16).padStart(2, '0')).join(''), width: image.width, height: image.height };
    } finally { image.close(); }
}

export function setupPdfLogoForms() {
    for (const form of document.querySelectorAll('[data-pdf-logo-form]')) {
        const original = form.querySelector('[name="logo"]');
        const derived = form.querySelector('[name="pdf_logo"]');
        const hash = form.querySelector('[name="pdf_logo_hash"]');
        const approved = form.querySelector('[name="pdf_logo_approved"]');
        const preview = form.querySelector('[data-pdf-logo-preview]');
        const message = form.querySelector('[data-pdf-logo-message]');
        let busy = false, objectUrl, generation = 0;
        async function prepare(blob) {
            const current = ++generation;
            busy = true; approved.checked = false; derived.value = ''; hash.value = ''; preview.hidden = true;
            message.textContent = 'Menyiapkan JPEG PDF…';
            try {
                const result = await convertPdfLogo(blob);
                if (current !== generation) return;
                const transfer = new DataTransfer(); transfer.items.add(result.file); derived.files = transfer.files;
                hash.value = result.hash;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(result.file); preview.src = objectUrl; preview.hidden = false;
                message.textContent = `JPEG PDF ${result.width} × ${result.height} piksel. Periksa detail logo dan setujui preview sebelum menyimpan.`;
            } catch (error) { if (current === generation) message.textContent = error.message; }
            finally { if (current === generation) busy = false; }
        }
        original.addEventListener('change', () => {
            if (original.files[0]) prepare(original.files[0]);
            else { generation++; busy = false; derived.value = ''; hash.value = ''; preview.hidden = true; approved.checked = false; }
        });
        form.querySelector('[data-prepare-existing-logo]').addEventListener('click', async () => {
            if (busy) return;
            try {
                if (original.files[0]) return await prepare(original.files[0]);
                const current = ++generation;
                busy = true;
                const response = await fetch(form.dataset.logoSource, { credentials: 'same-origin' });
                if (!response.ok) throw new Error('Logo saat ini gagal diambil. Periksa koneksi dan coba kembali.');
                const blob = await response.blob();
                if (current !== generation) return;
                await prepare(blob);
            } catch (error) { message.textContent = error.message; }
            finally { busy = false; }
        });
        form.addEventListener('submit', event => {
            const total = [...new FormData(form).values()].reduce((size, value) => size + (value instanceof File ? value.size : 0), 0);
            if (busy || (original.files.length && !derived.files.length) || (derived.files.length && !approved.checked) || total > 3670016) {
                event.preventDefault();
                message.textContent = busy ? 'Tunggu konversi selesai.' : total > 3670016 ? 'Total upload maksimal 3,5 MB. Simpan foto lain secara terpisah.' : 'Siapkan JPEG PDF dan setujui preview sebelum menyimpan.';
                message.scrollIntoView({ block: 'center' });
            }
        });
    }
}