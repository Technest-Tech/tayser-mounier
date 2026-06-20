<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookFileController extends Controller
{
    /**
     * Stream the readable file inline so it previews inside the browser.
     */
    public function preview(Book $book): StreamedResponse
    {
        $path = $this->resolvePath($book);

        return Storage::disk('local')->response($path, $this->downloadName($book, $path), [
            'Content-Disposition' => 'inline; filename="'.$this->downloadName($book, $path).'"',
        ]);
    }

    /**
     * Force-download the readable file.
     */
    public function download(Book $book): StreamedResponse
    {
        $path = $this->resolvePath($book);

        return Storage::disk('local')->download($path, $this->downloadName($book, $path));
    }

    /**
     * Resolve the only file a visitor may access for free, or 404.
     */
    protected function resolvePath(Book $book): string
    {
        abort_unless($book->isPublished(), 404);

        $path = $book->accessibleFilePath();

        abort_if(blank($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return $path;
    }

    protected function downloadName(Book $book, string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return Str::slug($book->title).($ext ? '.'.$ext : '');
    }
}
