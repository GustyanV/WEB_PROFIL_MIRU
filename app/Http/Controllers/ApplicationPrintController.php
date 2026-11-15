<?php

namespace App\Http\Controllers;

use App\Models\StudentApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Support\{PdfLogo,QrSvg};

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
        $logo = app(PdfLogo::class)->forProfile($school);
        $dimensions=getimagesizefromstring(base64_decode(explode(',',$logo,2)[1]));
        $scale=min(82/$dimensions[0],82/$dimensions[1]);

        return [
            'application' => $application,
            'a' => $application,
            'url' => $url,
            'logo' => $logo,
            'logo_width' => round($dimensions[0]*$scale,2),
            'logo_height' => round($dimensions[1]*$scale,2),
            'school' => $school,
            // SVG memakai backend bawaan BaconQrCode, sehingga tidak memerlukan Imagick.
            'qr' => base64_encode(app(QrSvg::class)->generate($url)),
        ];
    }

    private function authorizeApplicant(StudentApplication $application): void
    {
        abort_unless((int) auth()->id() === (int) $application->user_id, 403);
    }
}
