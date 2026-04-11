<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    private const SUPPORTED = ['es', 'en'];

    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (in_array($locale, self::SUPPORTED, true)) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
