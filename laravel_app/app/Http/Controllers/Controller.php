<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

abstract class Controller
{
    protected function ensureLoggedIn(): ?RedirectResponse
    {
        if (!Session::get('is_logged_in')) {
            return redirect()->route('login');
        }

        return null;
    }

    protected function ensureIsAdmin(): ?RedirectResponse
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user || !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return null;
    }
}
