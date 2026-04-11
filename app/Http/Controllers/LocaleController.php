<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    private const SUPPORTED = ['es', 'en'];

    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            Cookie::queue('locale_preference', $locale, 60 * 24 * 365);
        }

        return redirect()->back();
    }
}
