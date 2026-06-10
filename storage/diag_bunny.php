<?php
use Illuminate\Support\Facades\Http;
$lib = config('bunny.library_id'); $key = config('bunny.api_key');
$base = 'https://video.bunnycdn.com';
$guids = [
  'a8fc58b5-58e7-4511-be7e-7cf5c2fe88d8',
  '18591fb5-cee0-4d16-82fa-0d783c3b7247',
  '99ec097b-77dd-46bb-becf-99977130872a',
  '4b03d17c-ae32-4f96-bf38-b48a1b58b106',
];
foreach ($guids as $g) {
    $r = Http::withHeaders(['AccessKey'=>$key,'Accept'=>'application/json'])
        ->delete("$base/library/$lib/videos/$g");
    echo "DELETE $g -> HTTP {$r->status()} ".$r->body()."\n";
}
