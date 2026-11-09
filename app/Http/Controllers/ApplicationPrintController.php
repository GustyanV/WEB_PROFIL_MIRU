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
        if (!Str::isUuid($token)) return response()->view('public.verification', ['a'=>null], 404);
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

        $url = route('spmb.verify', ['token'=>$application->public_token]);
        $school = \App\Support\SchoolContent::profile(\App\Models\SchoolProfile::orderBy('id')->first());
        $logoBytes = is_file(public_path('images/logo-miru.png')) ? file_get_contents(public_path('images/logo-miru.png')) : null;
        if ($school?->logo && !str_contains($school->logo,'..')) {
            if (str_starts_with($school->logo,'images/')) {
                if (is_file(public_path($school->logo))) $logoBytes=file_get_contents(public_path($school->logo));
            } else {
                try {
                    $disk=\Illuminate\Support\Facades\Storage::disk('public');
                    if ($disk->exists($school->logo)) $logoBytes=$disk->get($school->logo);
                } catch (\Throwable $e) { report($e); } // Keep the packaged logo if cloud storage is temporarily unavailable.
            }
        }
        $logo = null;
        if ($logoBytes) {
            $bitmap=@imagecreatefromstring($logoBytes);
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
