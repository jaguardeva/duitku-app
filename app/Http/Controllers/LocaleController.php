<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
        ]);

        $locale = $validated['locale'];
        $request->session()->put('locale', $locale);
        cookie()->queue('catatduit_locale', $locale, 60 * 24 * 365);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        App::setLocale($locale);

        return back();
    }
}
