<?php

namespace App\Controllers;

use App\Models\BlacklistModel;

class WebController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/')->with('error', 'Unauthorized access');
        }
        return view('admin/dashboard');
    }
}
