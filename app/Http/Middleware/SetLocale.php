<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active locale from the session (set via the language switcher),
 * falling back to the app default. Keeps every request rendering in the user's
 * chosen language and direction.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.supported'));
        $locale = session('locale', config('app.locale'));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
