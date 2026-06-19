<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\User;
use App\Support\MailDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileMemberController extends Controller
{
    public function index(Request $request)
    {
        $owner = $request->user();

        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'message' => 'Hanya owner yang dapat mengakses anggota.',
            ], 403);
        }

        $business = $owner->business;

        if (!$business) {
            return response()->json([
                'business' => null,
                'members' => [],
            ]);
        }

        $members = BusinessMember::where('business_id', $business->id)
            ->with('user')
            ->orderBy('role')
            ->latest()
            ->get()
            ->map(fn (BusinessMember $member) => $this->formatMember($member))
            ->values();

        return response()->json([
            'business' => [
                'id' => $business->id,
                'nama_usaha' => $business->nama_usaha,
                'logo' => $business->logo_path,
            ],
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $owner = $request->user();

        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'message' => 'Hanya owner yang dapat mengundang anggota.',
            ], 403);
        }

        $business = $owner->business;

        if (!$business) {
            return response()->json([
                'message' => 'Lengkapi profil usaha terlebih dahulu sebelum menambahkan anggota.',
            ], 422);
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

        $email = strtolower(trim($validated['email']));
        $memberUser = User::where('email', $email)->first();

        if ($memberUser) {
            if ($memberUser->id === $owner->id) {
                return response()->json([
                    'message' => 'Owner tidak dapat mengundang akun sendiri.',
                ], 422);
            }

            if ($memberUser->role === 'owner' || $memberUser->hasBusinessAffiliation($business->id)) {
                return response()->json([
                    'message' => 'akun ini sudah terafiliasi dengan umkm lain',
                ], 422);
            }
        } else {
            $memberUser = User::create([
                'name' => Str::headline(Str::before($email, '@')),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        $acceptedMember = BusinessMember::where('business_id', $business->id)
            ->where('user_id', $memberUser->id)
            ->where('status', 'accepted')
            ->first();

        if ($acceptedMember) {
            return response()->json([
                'message' => 'Email ini sudah menjadi anggota aktif.',
            ], 422);
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
        $businessName = $business->nama_usaha;
        $roleLabel = $validated['role'] === 'sekretaris' ? 'Sekretaris' : 'Bendahara';
        $emailSent = true;
        $emailError = null;

        if (!MailDelivery::isInboxMailerConfigured()) {
            $emailSent = false;

            Log::info('Mobile invitation email skipped because SMTP is not configured for development.', [
                'business_id' => $business->id,
                'member_id' => $member->id,
                'email' => $memberUser->email,
                'invitation_link' => $acceptUrl,
            ]);
        } else {
            try {
                Mail::raw(
                    "Anda diundang sebagai {$roleLabel} untuk bergabung ke {$businessName}.\n\nKlik link berikut untuk menerima undangan dan membuat password:\n{$acceptUrl}",
                    function ($message) use ($memberUser, $businessName) {
                        $message->to($memberUser->email)
                            ->subject('Undangan Bisnis ' . $businessName);
                    }
                );
            } catch (\Throwable $e) {
                $emailSent = false;
                $emailError = $e->getMessage();

                Log::error('Mobile invite member email failed', [
                    'owner_id' => $owner->id,
                    'member_user_id' => $memberUser->id,
                    'email' => $memberUser->email,
                    'error' => $emailError,
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
            'member' => $this->formatMember($member->fresh(['user'])),
            'email_sent' => $emailSent,
            'invitation_link' => MailDelivery::allowsDevelopmentFallback() ? $acceptUrl : null,
            'mail_error' => MailDelivery::allowsDevelopmentFallback() ? $emailError : null,
        ], $emailSent || MailDelivery::allowsDevelopmentFallback() ? 201 : 502);
    }

    public function destroy(Request $request, BusinessMember $member)
    {
        $owner = $request->user();

        if (!$owner || $owner->role !== 'owner') {
            return response()->json([
                'message' => 'Hanya owner yang dapat menghapus anggota.',
            ], 403);
        }

        $business = $owner->business;

        if (!$business) {
            return response()->json([
                'message' => 'Profil usaha belum tersedia.',
            ], 422);
        }

        if ((int) $member->business_id !== (int) $business->id) {
            return response()->json([
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $memberUser = $member->user;
        $member->delete();

        if ($memberUser && !$memberUser->acceptedBusinessMembership() && !$memberUser->business()->exists()) {
            $memberUser->tokens()->delete();

            try {
                DB::table('sessions')->where('user_id', $memberUser->id)->delete();
            } catch (\Throwable $sessionError) {
                Log::warning('Unable to delete staff web sessions after mobile member removal', [
                    'user_id' => $memberUser->id,
                    'message' => $sessionError->getMessage(),
                ]);
            }

            $memberUser->delete();
        }

        return response()->json([
            'message' => 'Anggota berhasil dihapus.',
        ]);
    }

    private function formatMember(BusinessMember $member): array
    {
        return [
            'id' => $member->id,
            'business_id' => $member->business_id,
            'user_id' => $member->user_id,
            'name' => $member->user?->name,
            'email' => $member->user?->email ?? $member->invited_email,
            'role' => $member->role,
            'status' => $member->status,
            'invited_email' => $member->invited_email,
            'accepted_at' => optional($member->accepted_at)->toDateTimeString(),
            'created_at' => optional($member->created_at)->toDateTimeString(),
        ];
    }
}
