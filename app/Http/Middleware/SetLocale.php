<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the user's preferred locale for the current request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->user()->locale
            ?? $request->cookie('catatduit_locale', config('app.locale'));

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);
        cookie()->queue('catatduit_locale', $locale, 60 * 24 * 365);

        if ($request->user() && $request->user()->locale !== $locale) {
            $request->user()->update(['locale' => $locale]);
        }

        return $next($request);
    }
}
