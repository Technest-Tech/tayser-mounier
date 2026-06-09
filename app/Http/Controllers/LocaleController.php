<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the UI language and return the user to where they were.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('localization.supported'));

        if (in_array($locale, $supported, true)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
