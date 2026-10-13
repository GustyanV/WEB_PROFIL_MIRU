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
            $application->update(['public_token' => (string) Str::uuid()]);
        }

        $url = url('/verifikasi-spmb/'.$application->public_token);
        $logoPath = public_path('images/logo-miru.png');
        $logo = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        return [
            'application' => $application,
            'a' => $application,
            'url' => $url,
            'logo' => $logo,
            // SVG memakai backend bawaan BaconQrCode, sehingga tidak memerlukan Imagick.
            'qr' => base64_encode(QrCode::format('svg')->size(150)->generate($url)),
        ];
    }

    private function authorizeApplicant(StudentApplication $application): void
    {
        abort_unless(auth()->id() === $application->user_id, 403);
    }
}
