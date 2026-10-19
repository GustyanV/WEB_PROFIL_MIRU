<?php

namespace App\Http\Controllers;

use App\Models\StudentApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ApplicationPrintController extends Controller
{
    public function preview(StudentApplication $application)
    {
        $this->authorizeApplicant($application);

        return view('dashboard.proof', $this->proofData($application));
    }

    public function pdf(StudentApplication $application)
    {
        $this->authorizeApplicant($application);

        return Pdf::loadView('pdf.proof', $this->proofData($application))
            ->setPaper('a4', 'portrait')
            ->download('Bukti-SPMB-'.$application->registration_number.'.pdf');
    }

    public function adminPdf(StudentApplication $application)
    {
        return Pdf::loadView('pdf.proof', $this->proofData($application))
            ->setPaper('a4', 'portrait')
            ->download('Bukti-SPMB-'.$application->registration_number.'.pdf');
    }

    public function verify(string $token)
    {
        $a = StudentApplication::query()
            ->where('public_token', $token)
            ->with(['studentProfile', 'period', 'wave'])
            ->first();

        if (! $a) {
            return response()->view('public.verification', compact('a'), 404);
        }

        return view('public.verification', compact('a'));
    }

    private function proofData(StudentApplication $application): array
    {
        $application->load(['user', 'studentProfile', 'parentProfile', 'period', 'wave']);

        abort_unless(
            $application->registration_number,
            422,
            'Silakan selesaikan dan kirim pendaftaran terlebih dahulu.'
        );

        if (! $application->public_token) {
            $application->forceFill(['public_token' => (string) Str::uuid()])->save();
        }

        $url = url('/verifikasi-spmb/'.$application->public_token);
        $school = \App\Support\SchoolContent::profile(\App\Models\SchoolProfile::orderBy('id')->first());
        $logoPath = public_path('images/logo-miru.png');
        if ($school?->logo && !str_contains($school->logo,'..')) {
            $candidate = str_starts_with($school->logo,'images/') ? public_path($school->logo) : storage_path('app/public/'.$school->logo);
            if (is_file($candidate)) $logoPath=$candidate;
        }
        $logo = null;
        if (is_file($logoPath)) {
            $bitmap=@imagecreatefromstring(file_get_contents($logoPath));
            if ($bitmap) {
                ob_start(); imagepng($bitmap); $bytes=ob_get_clean(); imagedestroy($bitmap);
                $logo='data:image/png;base64,'.base64_encode($bytes);
            }
        }

        return [
            'application' => $application,
            'a' => $application,
            'url' => $url,
            'logo' => $logo,
            'school' => $school,
            // SVG memakai backend bawaan BaconQrCode, sehingga tidak memerlukan Imagick.
            'qr' => base64_encode(QrCode::format('svg')->size(150)->generate($url)),
        ];
    }

    private function authorizeApplicant(StudentApplication $application): void
    {
        abort_unless((int) auth()->id() === (int) $application->user_id, 403);
    }
}
