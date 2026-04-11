<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['es', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('locale_preference');

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = $request->getPreferredLanguage(self::SUPPORTED) ?? config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
