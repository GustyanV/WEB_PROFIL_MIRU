<?php
namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Services\SpmbDocuments;

class SpmbDocumentController extends Controller
{
    public function show(ApplicationDocument $document)
    {
        abort_unless(SpmbDocuments::validPath($document->file_path), 404);
        abort_unless(SpmbDocuments::disk()->exists($document->file_path), 404);
        return SpmbDocuments::disk()->response($document->file_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function applicant(ApplicationDocument $document)
    {
        abort_unless((int) $document->application->user_id === (int) auth()->id(), 403);
        return $this->show($document);
    }
}
