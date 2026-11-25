<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function show(Request $request)
    {
        $english = $request->routeIs('landing.en');
        $locale = $english ? 'en' : 'es';
        App::setLocale($locale);
        if ($request->isMethod('GET')) {
            Cookie::queue('locale_preference', $locale, 60 * 24 * 365);
            if (! $request->user()) {
                $request->session()->put('signup_landing_locale', $locale);
                $key = 'visited_landing.'.$locale;
                if (! $request->session()->has($key)) {
                    $bucket = ['day' => today()->toDateString(), 'locale' => $locale];
                    DB::table('landing_visits')->insertOrIgnore($bucket + ['sessions' => 0]);
                    DB::table('landing_visits')->where($bucket)->increment('sessions');
                    $request->session()->put($key, true);
                }
            }
        }

        return response()->view('landing', ['english' => $english])
            ->header('Content-Language', $locale);
    }
}
