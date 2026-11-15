<?php
namespace App\Console\Commands;
use App\Models\SchoolProfile;
use App\Support\PdfLogo;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;
class CheckPdfLogo extends Command
{
 protected $signature='spmb:check-pdf-logo';
 protected $description='Read-only check for GD-less PDF logo readiness';
 public function handle(PdfLogo $logos):int {
  try {$logos->forProfile(SchoolProfile::orderBy('id')->first());$this->info('Logo PDF siap: JPEG valid, tanpa konversi GD.');return self::SUCCESS;}
  catch(ValidationException $e){$this->error(implode(' ',array_merge(...array_values($e->errors()))));$this->line('Buka Profil Madrasah atau Pengaturan Website, siapkan JPEG PDF, periksa preview, lalu simpan. File asli dipertahankan.');return self::FAILURE;}
  catch(\Throwable $e){$this->error('Tidak dapat membaca sumber logo/Storage. Credential tidak ditampilkan.');return self::FAILURE;}
 }
}