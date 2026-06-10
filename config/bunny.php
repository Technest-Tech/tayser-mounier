<?php

return [
    /*
    | Bunny Stream library id (numeric).
    */
    'library_id' => env('BUNNY_LIBRARY_ID'),

    /*
    | Bunny Stream API key (for management API calls, if needed).
    */
    'api_key' => env('BUNNY_API_KEY'),

    /*
    | Pull-zone token authentication key — used to sign short-lived playback URLs.
    | Enable "Token Authentication" on the pull zone in the Bunny dashboard.
    */
    'token_auth_key' => env('BUNNY_TOKEN_AUTH_KEY'),

    /*
    | The CDN hostname of the stream pull zone, e.g. "vz-xxxxxxxx.b-cdn.net".
    */
    'cdn_hostname' => env('BUNNY_CDN_HOSTNAME'),

    /*
    | The iframe player host (Bunny embed). Usually "iframe.mediadelivery.net".
    */
    'player_host' => env('BUNNY_PLAYER_HOST', 'iframe.mediadelivery.net'),

    /*
    | How long (seconds) a signed playback URL stays valid.
    */
    'url_ttl' => (int) env('BUNNY_URL_TTL', 3600),
];
