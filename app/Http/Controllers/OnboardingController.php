<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->role) {
            $user->role = 'owner';
            $user->save();
        }

        return redirect()->route('dashboard');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->role = 'owner';
        $user->save();

        $request->session()->forget('show_role_onboarding');

        return redirect()->route('dashboard');
    }
}
