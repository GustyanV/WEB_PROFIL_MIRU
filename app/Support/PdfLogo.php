<?php
namespace App\Support;

use App\Models\SchoolProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class PdfLogo
{
    // Binds the packaged JPEG to the exact original used by the browser conversion.
    private const PACKAGED_SOURCE_SHA256 = '1671f7dbc72f4f73a7f722c0dc5f504b1efc0648175495c52691222c235ac71d';
    public function source(?SchoolProfile $profile): string
    {
        $path=$profile?->logo;
        if (!$path) return file_get_contents(public_path('images/logo-miru.png'));
        if (str_contains($path,'..')) $this->invalid('Lokasi logo tidak valid.');
        if (str_starts_with($path,'images/')) {
            if (is_file(public_path($path))) return file_get_contents(public_path($path));
        } elseif (Storage::disk('public')->exists($path)) return Storage::disk('public')->get($path);
        $this->invalid('Logo asli tidak ditemukan. Periksa logo pada Profil Madrasah.');
    }

    public function key(string $original): string
    {
        return 'school/pdf-logos/'.hash('sha256',$original).'.jpg';
    }

    public function validatePair(string $original, string $jpeg, ?string $hash): void
    {
        $a=@getimagesizefromstring($original); $b=@getimagesizefromstring($jpeg);
        if (!$a || !$b || $b['mime']!=='image/jpeg' || $a[0]!==$b[0] || $a[1]!==$b[1] || !hash_equals(hash('sha256',$original),(string)$hash)) {
            $this->invalid('JPEG PDF tidak cocok dengan logo asli. Buat ulang preview dan simpan kembali.');
        }
    }

    public function validateUpload(\Illuminate\Http\Request $request, ?SchoolProfile $profile): array
    {
        $original=$request->hasFile('logo') ? file_get_contents($request->file('logo')->getRealPath()) : null;
        $jpeg=$request->hasFile('pdf_logo') ? file_get_contents($request->file('pdf_logo')->getRealPath()) : null;
        if ($jpeg) {
            if (!$request->boolean('pdf_logo_approved')) throw ValidationException::withMessages(['pdf_logo_approved'=>'Periksa preview JPEG PDF dan setujui sebelum menyimpan.']);
            $original=$original ?? $this->source($profile);
            $this->validatePair($original,$jpeg,$request->input('pdf_logo_hash'));
        }
        if ($original) {
            $size=@getimagesizefromstring($original);
            if (!$size || $size[0]*$size[1]>20000000) $this->invalid('Logo maksimal 20 juta piksel.');
            if (!$jpeg && $size['mime']!=='image/jpeg') $this->invalid('Siapkan JPEG PDF sebelum menyimpan logo baru.');
        }
        $total=0; foreach($request->allFiles() as $file) if($file instanceof \Illuminate\Http\UploadedFile) $total+=$file->getSize();
        if ($total>3670016) $this->invalid('Total upload maksimal 3,5 MB. Unggah foto lain secara terpisah.');
        return [$original,$jpeg];
    }

    public function storeDerivative(string $original, string $jpeg): ?string
    {
        $disk=Storage::disk('public');$key=$this->key($original);
        if ($disk->exists($key)) return null;
        if (!$disk->put($key,$jpeg)) $this->invalid('JPEG PDF gagal disimpan.');
        return $key;
    }

    public function forProfile(?SchoolProfile $profile): string
    {
        $original=$this->source($profile);
        $size=@getimagesizefromstring($original);
        if ($size && $size['mime']==='image/jpeg') $jpeg=$original;
        elseif (hash_equals(self::PACKAGED_SOURCE_SHA256,hash('sha256',$original))) $jpeg=file_get_contents(public_path('images/logo-miru-pdf.jpg'));
        else {
            $disk=Storage::disk('public');$key=$this->key($original);
            if (!$disk->exists($key)) $this->invalid('Logo PDF belum disiapkan. Admin perlu membuka Profil Madrasah, menyiapkan JPEG PDF, lalu menyimpan hasil preview.');
            $jpeg=$disk->get($key);
        }
        $this->validatePair($original,$jpeg,hash('sha256',$original));
        return 'data:image/jpeg;base64,'.base64_encode($jpeg);
    }

    private function invalid(string $message): void
    {
        throw ValidationException::withMessages(['pdf_logo'=>$message]);
    }
}