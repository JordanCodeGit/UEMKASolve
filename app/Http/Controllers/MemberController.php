<?php

namespace App\Http\Controllers;

use App\Models\BusinessMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

        $memberUser = User::where('email', $validated['email'])->first();
        if (!$memberUser) {
            return response()->json(['message' => 'email belum terdaftar'], 422);
        }

        if ($memberUser->id === $owner->id) {
            return response()->json(['message' => 'Owner tidak dapat mengundang akun sendiri.'], 422);
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
                'invited_email' => $memberUser->email,
                'accepted_at' => null,
            ]
        );

        $acceptUrl = route('members.accept', $token);
        $localAcceptUrl = url('/invitations/' . $token . '/accept');
        $ownerName = $owner->name;
        $businessName = $business->nama_usaha;

        Mail::raw(
            "Anda diundang untuk bergabung ke bisnis {$businessName} dari owner {$ownerName} sebagai {$validated['role']}.\n\nKlik link berikut untuk menerima undangan:\n{$acceptUrl}",
            function ($message) use ($memberUser, $businessName) {
                $message->to($memberUser->email)
                    ->subject('Undangan Bisnis ' . $businessName);
            }
        );

        return response()->json([
            'message' => 'Undangan anggota berhasil dikirim.',
            'member' => $member->load('user'),
            'invitation_link' => config('mail.default') === 'log' ? $localAcceptUrl : null,
        ]);
    }

    public function accept(string $token)
    {
        $member = BusinessMember::where('invite_token', $token)
            ->where('status', 'pending')
            ->with(['user', 'business'])
            ->firstOrFail();

        $member->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'invite_token' => null,
        ]);

        $member->user->role = $member->role;
        $member->user->save();

        Auth::login($member->user, true);
        request()->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Undangan bisnis berhasil diterima.');
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

        if (!$owner->business || $member->business_id !== $owner->business->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $member->delete();

        return response()->json(['message' => 'Anggota berhasil dihapus.']);
    }
}
