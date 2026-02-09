<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Switch application language
     *
     * @param Request $request
     * @param string $locale
     * @return RedirectResponse
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        // Validate locale
        if (!in_array($locale, ['en', 'vi'])) {
            abort(400, 'Invalid locale');
        }

        // Store locale in session
        session(['locale' => $locale]);

        // Redirect back to previous page
        return redirect()->back();
    }
}
