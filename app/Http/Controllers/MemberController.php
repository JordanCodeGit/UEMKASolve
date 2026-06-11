<?php

namespace App\Http\Controllers;

use App\Models\BusinessMember;
use App\Models\User;
use App\Support\MailDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'owner') {
            return redirect()->route('dashboard');
        }

        $business = $user->business;

        $members = $business
            ? $business->members()->with('user')->orderBy('role')->latest()->get()
            : collect();

        return view('anggota', [
            'members' => $members,
            'business' => $business,
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $owner */
        $owner = Auth::user();
        $business = $owner->business;

        if (!$business || $owner->role !== 'owner') {
            return response()->json(['message' => 'Hanya owner yang dapat mengundang anggota.'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in(['sekretaris', 'bendahara'])],
        ]);

        if (!MailDelivery::isInboxMailerConfigured() && !MailDelivery::allowsDevelopmentFallback()) {
            return response()->json([
                'message' => MailDelivery::configurationErrorMessage(),
            ], 503);
        }

        $email = strtolower($validated['email']);
        $memberUser = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::headline(Str::before($email, '@')),
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        if ($memberUser->id === $owner->id) {
            return response()->json(['message' => 'Owner tidak dapat mengundang akun sendiri.'], 422);
        }

        if ($memberUser->role === 'owner' && $memberUser->business) {
            return response()->json(['message' => 'Email ini sudah terdaftar sebagai owner bisnis lain.'], 422);
        }

        $acceptedMember = BusinessMember::where('business_id', $business->id)
            ->where('user_id', $memberUser->id)
            ->where('status', 'accepted')
            ->first();

        if ($acceptedMember) {
            return response()->json(['message' => 'Email ini sudah menjadi anggota aktif.'], 422);
        }

        $token = Str::random(64);
        $member = BusinessMember::updateOrCreate(
            [
                'business_id' => $business->id,
                'user_id' => $memberUser->id,
            ],
            [
                'role' => $validated['role'],
                'status' => 'pending',
                'invite_token' => $token,
                'invited_email' => $email,
                'accepted_at' => null,
            ]
        );

        $acceptUrl = route('members.accept', $token);
        $localAcceptUrl = url('/invitations/' . $token . '/accept');
        $ownerName = $owner->name;
        $businessName = $business->nama_usaha;

        $emailSent = true;
        $emailError = null;

        if (!MailDelivery::isInboxMailerConfigured()) {
            $emailSent = false;

            Log::info('Invitation email skipped because SMTP is not configured for development.', [
                'business_id' => $business->id,
                'member_id' => $member->id,
                'email' => $memberUser->email,
                'invitation_link' => $acceptUrl,
            ]);
        } else {
            try {
                Mail::raw(
                    "Anda diundang untuk bergabung ke bisnis {$businessName} dari owner {$ownerName} sebagai {$validated['role']}.\n\nKlik link berikut untuk membuat password dan menerima undangan:\n{$acceptUrl}",
                    function ($message) use ($memberUser, $businessName) {
                        $message->to($memberUser->email)
                            ->subject('Undangan Bisnis ' . $businessName);
                    }
                );
            } catch (\Throwable $mailError) {
                $emailSent = false;
                $emailError = $mailError->getMessage();

                Log::error('Invitation email failed', [
                    'business_id' => $business->id,
                    'member_id' => $member->id,
                    'email' => $memberUser->email,
                    'message' => $emailError,
                    'invitation_link' => $acceptUrl,
                ]);
            }
        }

        return response()->json([
            'message' => $emailSent
                ? 'Undangan anggota berhasil dikirim.'
                : (MailDelivery::allowsDevelopmentFallback()
                    ? 'Undangan anggota berhasil dibuat untuk pengujian lokal. Gunakan link undangan di bawah.'
                    : 'Undangan anggota belum dapat dikirim. Periksa konfigurasi SMTP hosting.'),
            'member' => $member->load('user'),
            'email_sent' => $emailSent,
            'invitation_link' => MailDelivery::allowsDevelopmentFallback()
                ? $localAcceptUrl
                : null,
            'mail_error' => MailDelivery::allowsDevelopmentFallback() ? $emailError : null,
        ], $emailSent || MailDelivery::allowsDevelopmentFallback() ? 200 : 502);
    }

    public function accept(string $token)
    {
        $member = BusinessMember::where('invite_token', $token)
            ->where('status', 'pending')
            ->with(['user', 'business'])
            ->firstOrFail();

        return view('auth.create-password', [
            'token' => $token,
            'member' => $member,
            'email' => $member->user->email,
        ]);
    }

    public function completeInvitation(Request $request, string $token)
    {
        $member = BusinessMember::where('invite_token', $token)
            ->where('status', 'pending')
            ->with(['user', 'business'])
            ->firstOrFail();

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $member->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'invite_token' => null,
        ]);

        $member->user->role = $member->role;
        $member->user->password = Hash::make($validated['password']);
        $member->user->email_verified_at = $member->user->email_verified_at ?? now();
        $member->user->remember_token = Str::random(60);
        $member->user->save();

        Auth::login($member->user, true);
        request()->session()->regenerate();

        $token = $member->user->createToken('invitation-login')->plainTextToken;

        return response()->view('auth.session-token-callback', [
            'token' => $token,
            'next' => route('dashboard', absolute: false),
        ]);
    }

    public function acceptPending(BusinessMember $member)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($member->user_id !== $user->id || $member->status !== 'pending') {
            return response()->json(['message' => 'Undangan tidak valid.'], 403);
        }

        $member->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'invite_token' => null,
        ]);

        $user->role = $member->role;
        $user->save();
        request()->session()->forget('show_role_onboarding');

        return response()->json([
            'message' => 'Undangan bisnis berhasil diterima.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function rejectPending(BusinessMember $member)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($member->user_id !== $user->id || $member->status !== 'pending') {
            return response()->json(['message' => 'Undangan tidak valid.'], 403);
        }

        $member->delete();

        return response()->json([
            'message' => 'Undangan bisnis ditolak.',
            'redirect' => route('dashboard'),
        ]);
    }

    public function destroy(BusinessMember $member)
    {
        /** @var \App\Models\User $owner */
        $owner = Auth::user();
        $business = $owner->role === 'owner'
            ? $owner->business()->first()
            : null;

        if (!$business) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $memberToDelete = $business->members()
            ->whereKey($member->getKey())
            ->first();

        if (!$memberToDelete) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $removedUser = $memberToDelete->user;
        $memberToDelete->delete();

        if ($removedUser && !$removedUser->acceptedBusinessMembership()) {
            $removedUser->tokens()->delete();
        }

        return response()->json(['message' => 'Anggota berhasil dihapus.']);
    }
}
