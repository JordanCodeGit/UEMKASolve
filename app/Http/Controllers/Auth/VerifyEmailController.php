<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request, $id, $hash): RedirectResponse
    {
        // Verify the signed URL first
        if (! $request->hasValidSignature()) {
            abort(403, 'This action is unauthorized.');
        }

        // Find the user by id from the route
        $user = User::findOrFail($id);

        // Validate the hash matches the user's email
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'This action is unauthorized.');
        }

        // Redirect URL base (frontend or app.url)
        $configUrl = config('app.frontend_url');
        $frontendUrl = is_string($configUrl) ? $configUrl : (is_string(config('app.url')) ? config('app.url') : '');

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($frontendUrl . '/dashboard?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended($frontendUrl . '/dashboard?verified=1');
    }
}
