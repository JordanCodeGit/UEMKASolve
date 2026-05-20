<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role) {
            return redirect()->route('dashboard');
        }

        if (!session('show_role_onboarding')) {
            return redirect()->route('dashboard');
        }

        return view('onboarding');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['owner', 'sekretaris', 'bendahara'])],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->role = $validated['role'];
        $user->save();

        $request->session()->forget('show_role_onboarding');

        return redirect()->route('dashboard');
    }
}
