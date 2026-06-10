<?php

namespace App\Services\Bunny;

use App\Models\Lesson;

/**
 * Builds short-lived, token-authenticated Bunny Stream playback URLs.
 *
 * Bunny token authentication: the token is
 *   base64url( sha256_raw( tokenKey + path + expires ) )
 * appended as ?token=...&expires=... The embed/iframe then only plays while the
 * token is valid, so raw URLs cannot be shared long-term.
 *
 * Enable "Token Authentication" on the Stream pull zone for this to take effect.
 */
class BunnySignedUrlService
{
    /**
     * Signed iframe embed URL for a Bunny lesson.
     */
    public function embedUrl(Lesson $lesson): string
    {
        $libraryId = config('bunny.library_id');
        $playerHost = config('bunny.player_host');
        $videoId = $lesson->video_id;

        $path = "/embed/{$libraryId}/{$videoId}";
        [$token, $expires] = $this->signPath($path);

        return "https://{$playerHost}{$path}?token={$token}&expires={$expires}";
    }

    /**
     * Signed HLS playlist URL (if you build a custom player instead of the iframe).
     */
    public function hlsUrl(Lesson $lesson): string
    {
        $cdn = config('bunny.cdn_hostname');
        $videoId = $lesson->video_id;

        $path = "/{$videoId}/playlist.m3u8";
        [$token, $expires] = $this->signPath($path);

        return "https://{$cdn}{$path}?token={$token}&expires={$expires}";
    }

    /**
     * Poster/thumbnail image for a Bunny video. Bunny auto-generates
     * `thumbnail.jpg` once processing finishes. Signed only when token auth is
     * enabled (an unsigned token would otherwise be rejected by the pull zone).
     */
    public function thumbnailUrl(Lesson $lesson): string
    {
        $cdn = config('bunny.cdn_hostname');
        $path = "/{$lesson->video_id}/thumbnail.jpg";

        if (blank(config('bunny.token_auth_key'))) {
            return "https://{$cdn}{$path}";
        }

        [$token, $expires] = $this->signPath($path);

        return "https://{$cdn}{$path}?token={$token}&expires={$expires}";
    }

    public function isConfigured(): bool
    {
        return filled(config('bunny.token_auth_key'))
            && filled(config('bunny.library_id'));
    }

    /**
     * @return array{0: string, 1: int} [token, expires]
     */
    private function signPath(string $path): array
    {
        $key = (string) config('bunny.token_auth_key');
        $expires = now()->addSeconds((int) config('bunny.url_ttl'))->timestamp;

        $hash = hash('sha256', $key . $path . $expires, true);
        $token = $this->base64Url($hash);

        return [$token, $expires];
    }

    private function base64Url(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }
}
