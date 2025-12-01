<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Bagus: Assertion ini krusial untuk PHPStan Level 9 karena
        // $request->user() secara default return type-nya nullable (User|null).
        assert($user !== null);

        // Use ternary operator to safely convert mixed config to string
        $configUrl = config('app.frontend_url');
        $frontendUrl = is_string($configUrl) ? $configUrl : (is_string(config('app.url')) ? config('app.url') : '');

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(
                $frontendUrl . '/dashboard?verified=1'
            );
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(
            $frontendUrl . '/dashboard?verified=1'
        );
    }
}
