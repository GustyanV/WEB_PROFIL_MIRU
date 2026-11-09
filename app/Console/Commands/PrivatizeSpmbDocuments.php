<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\SpmbDocuments;

class PrivatizeSpmbDocuments extends Command
{
    protected $signature = 'spmb:privatize-documents';
    protected $description = 'Pindahkan berkas SPMB publik ke penyimpanan privat setelah verifikasi isi';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = SpmbDocuments::disk();
        $count = 0;
        foreach ($public->allFiles('spmb') as $path) {
            if (!SpmbDocuments::validPath($path)) {
                $this->error('Path berkas tidak valid. Tidak ada penghapusan untuk berkas tersebut.');
                return 1;
            }
            $bytes = $public->get($path);
            if (!$private->exists($path)) $private->put($path, $bytes);
            if (!$private->exists($path) || !hash_equals(hash('sha256', $bytes), hash('sha256', $private->get($path)))) {
                $this->error('Salinan privat berbeda atau gagal. Berkas publik dipertahankan.');
                return 1;
            }
            if (!$public->delete($path)) {
                $this->error('Salinan privat sudah aman, tetapi penghapusan salinan publik gagal.');
                return 1;
            }
            $count++;
        }
        $this->info($count.' berkas SPMB dipindahkan dan diverifikasi; record database tidak diubah.');
        return 0;
    }
}
