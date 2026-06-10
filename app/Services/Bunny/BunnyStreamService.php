<?php

namespace App\Services\Bunny;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Talks to the Bunny Stream management API so admins can upload a video file
 * straight from the Filament panel instead of pre-uploading in the Bunny
 * dashboard and pasting a GUID.
 *
 * Flow (per Bunny docs):
 *   1. POST   /library/{lib}/videos        -> create a video object, get its guid
 *   2. PUT    /library/{lib}/videos/{guid} -> stream the file bytes into it
 *
 * The guid we get back is exactly what we store in `lessons.video_id`, which the
 * existing BunnySignedUrlService then turns into a signed playback URL.
 */
class BunnyStreamService
{
    private const BASE = 'https://video.bunnycdn.com';

    public function isConfigured(): bool
    {
        return filled(config('bunny.api_key')) && filled(config('bunny.library_id'));
    }

    /**
     * Create the video object then upload the file bytes. Returns the Bunny guid.
     *
     * @param  string  $absolutePath  A readable path to the video file on disk.
     */
    public function upload(string $title, string $absolutePath): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Bunny Stream is not configured (set BUNNY_API_KEY and BUNNY_LIBRARY_ID).');
        }

        if (! is_readable($absolutePath)) {
            throw new RuntimeException("Uploaded file is not readable: {$absolutePath}");
        }

        $guid = $this->createVideo($title);
        $this->uploadBytes($guid, $absolutePath);

        return $guid;
    }

    /**
     * Step 1 — register an (empty) video and return its guid.
     */
    public function createVideo(string $title): string
    {
        $library = config('bunny.library_id');

        try {
            $response = Http::withHeaders([
                'AccessKey' => (string) config('bunny.api_key'),
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post(self::BASE."/library/{$library}/videos", [
                    'title' => $title,
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Could not reach Bunny Stream: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new RuntimeException("Bunny rejected the create-video request (HTTP {$response->status()}): {$response->body()}");
        }

        $guid = $response->json('guid');

        if (blank($guid)) {
            throw new RuntimeException('Bunny did not return a video guid.');
        }

        return $guid;
    }

    /**
     * Step 2 — stream the file into the video object. Streamed (not loaded into
     * memory) so large lecture recordings don't blow the PHP memory limit.
     */
    private function uploadBytes(string $guid, string $absolutePath): void
    {
        $library = config('bunny.library_id');
        $handle = fopen($absolutePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open file for upload: {$absolutePath}");
        }

        try {
            $response = Http::withHeaders([
                'AccessKey' => (string) config('bunny.api_key'),
                'Content-Type' => 'application/octet-stream',
            ])
                ->timeout((int) config('bunny.upload_timeout', 1800))
                ->withOptions(['body' => $handle])
                ->put(self::BASE."/library/{$library}/videos/{$guid}");
        } catch (ConnectionException $e) {
            throw new RuntimeException('Upload to Bunny failed: '.$e->getMessage(), previous: $e);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        if ($response->failed()) {
            throw new RuntimeException("Bunny rejected the upload (HTTP {$response->status()}): {$response->body()}");
        }
    }
}
