<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch language locale.
     */
    public function switch(string $lang)
    {
        if (in_array($lang, ['id', 'en'])) {
            Session::put('locale', $lang);
        }

        return redirect()->back();
    }
}
