<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleDataSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretary_categories_are_shared_and_treasurer_cannot_create_categories(): void
    {
        [$owner, $business, $secretary, $treasurer] = $this->createBusinessTeam();

        Sanctum::actingAs($secretary);

        $createResponse = $this->postJson('/api/categories', [
            'nama_kategori' => 'Penjualan',
            'tipe' => 'pemasukan',
            'ikon' => 'pemasukan/Button.png',
        ]);

        $createResponse->assertCreated();

        $duplicateResponse = $this->postJson('/api/categories', [
            'nama_kategori' => 'penjualan',
            'tipe' => 'pemasukan',
            'ikon' => 'pemasukan/Button-1.png',
        ]);

        $duplicateResponse->assertStatus(422);

        Sanctum::actingAs($treasurer);

        $this->getJson('/api/categories?tipe=pemasukan')
            ->assertOk()
            ->assertJsonFragment([
                'nama_kategori' => 'Penjualan',
                'business_id' => $business->id,
            ]);

        $this->postJson('/api/categories', [
            'nama_kategori' => 'Operasional',
            'tipe' => 'pengeluaran',
            'ikon' => 'pengeluaran/Button.png',
        ])->assertForbidden();
    }

    public function test_owner_can_create_and_see_business_transactions(): void
    {
        [$owner, $business] = $this->createBusinessTeam();

        $category = Category::create([
            'business_id' => $business->id,
            'nama_kategori' => 'Modal',
            'tipe' => 'pemasukan',
            'ikon' => 'pemasukan/Button.png',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/transactions', [
            'category_id' => $category->id,
            'jumlah' => 250000,
            'tanggal_transaksi' => '2026-06-10 10:00:00',
            'catatan' => 'Setoran awal',
        ])->assertCreated();

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('pagination.data.0.business_id', $business->id)
            ->assertJsonPath('pagination.data.0.category.nama_kategori', 'Modal');
    }

    public function test_secretary_dashboard_url_redirects_to_buku_kas(): void
    {
        [$owner, $business, $secretary] = $this->createBusinessTeam();

        $this->actingAs($secretary)
            ->get('/dashboard')
            ->assertRedirect(route('buku-kas'));
    }

    public function test_treasurer_repeated_transaction_submit_only_creates_one_record(): void
    {
        Cache::flush();

        [$owner, $business, $secretary, $treasurer] = $this->createBusinessTeam();

        $category = Category::create([
            'business_id' => $business->id,
            'nama_kategori' => 'Operasional',
            'tipe' => 'pengeluaran',
            'ikon' => 'pengeluaran/Button.png',
        ]);

        Sanctum::actingAs($treasurer);

        $payload = [
            'category_id' => $category->id,
            'jumlah' => 75000,
            'tanggal_transaksi' => '2026-06-11 09:30:00',
            'catatan' => 'Pembelian alat tulis',
        ];

        $firstResponse = $this->postJson('/api/transactions', $payload);
        $secondResponse = $this->postJson('/api/transactions', $payload);

        $firstResponse->assertCreated();
        $secondResponse->assertOk()
            ->assertJsonPath('id', $firstResponse->json('id'));

        $this->assertSame(1, Transaction::where('business_id', $business->id)
            ->where('category_id', $category->id)
            ->where('catatan', 'Pembelian alat tulis')
            ->count());
    }

    public function test_secretary_can_see_treasurer_transactions_from_same_business(): void
    {
        [$owner, $business, $secretary, $treasurer] = $this->createBusinessTeam();

        $category = Category::create([
            'business_id' => $business->id,
            'nama_kategori' => 'Operasional',
            'tipe' => 'pengeluaran',
            'ikon' => 'pengeluaran/Button.png',
        ]);

        Sanctum::actingAs($treasurer);

        $transaction = $this->postJson('/api/transactions', [
            'category_id' => $category->id,
            'jumlah' => 125000,
            'tanggal_transaksi' => '2026-06-11 10:00:00',
            'catatan' => 'Pembelian perlengkapan kantor',
        ])->assertCreated()->json();

        Sanctum::actingAs($secretary);

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('pagination.data.0.id', $transaction['id'])
            ->assertJsonPath('pagination.data.0.business_id', $business->id)
            ->assertJsonPath('pagination.data.0.catatan', 'Pembelian perlengkapan kantor');
    }

    public function test_secretary_receipt_is_available_only_for_transactions_saved_with_ocr_receipt(): void
    {
        Storage::fake('public');

        [$owner, $business, $secretary, $treasurer] = $this->createBusinessTeam();

        $category = Category::create([
            'business_id' => $business->id,
            'nama_kategori' => 'Operasional',
            'tipe' => 'pengeluaran',
            'ikon' => 'pengeluaran/Button.png',
        ]);

        $receiptPath = 'receipts/struk-test.jpg';
        Storage::disk('public')->put($receiptPath, 'receipt image');

        Sanctum::actingAs($treasurer);

        $manualTransaction = $this->postJson('/api/transactions', [
            'category_id' => $category->id,
            'jumlah' => 50000,
            'tanggal_transaksi' => '2026-06-11 09:00:00',
            'catatan' => 'Input manual',
        ])->assertCreated()->json();

        $ocrTransaction = $this->postJson('/api/transactions', [
            'category_id' => $category->id,
            'jumlah' => 75000,
            'tanggal_transaksi' => '2026-06-11 10:00:00',
            'catatan' => 'Dari scan OCR',
            'receipt_path' => $receiptPath,
        ])->assertCreated()
            ->assertJsonPath('receipt_path', $receiptPath)
            ->json();

        Sanctum::actingAs($secretary);

        $this->getJson('/api/transactions?per_page=10')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $ocrTransaction['id'],
                'receipt_path' => $receiptPath,
            ])
            ->assertJsonFragment([
                'id' => $manualTransaction['id'],
                'receipt_path' => null,
            ]);

        $this->actingAs($secretary)
            ->get(route('transactions.receipt', $ocrTransaction['id']))
            ->assertOk();

        $this->actingAs($secretary)
            ->get(route('transactions.receipt', $manualTransaction['id']))
            ->assertNotFound();
    }

    public function test_only_verified_transactions_affect_cash_summary_and_flagged_requires_audit_note(): void
    {
        [$owner, $business, $secretary, $treasurer] = $this->createBusinessTeam();

        $category = Category::create([
            'business_id' => $business->id,
            'nama_kategori' => 'Penjualan',
            'tipe' => 'pemasukan',
            'ikon' => 'pemasukan/Button.png',
        ]);

        Sanctum::actingAs($owner);

        $pendingTransaction = $this->postJson('/api/transactions', [
            'category_id' => $category->id,
            'jumlah' => 100000,
            'tanggal_transaksi' => '2026-06-10 10:00:00',
            'catatan' => 'Belum audit',
        ])->assertCreated()->json();

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('summary.total_pemasukan', 0);

        Sanctum::actingAs($secretary);

        $this->patchJson('/api/transactions/' . $pendingTransaction['id'] . '/status', [
            'status' => 'flagged',
        ])->assertStatus(422);

        $this->patchJson('/api/transactions/' . $pendingTransaction['id'] . '/status', [
            'status' => 'flagged',
            'audit_note' => 'Nominal perlu dicek ulang.',
        ])->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id' => $pendingTransaction['id'],
            'status' => 'flagged',
            'audit_note' => 'Nominal perlu dicek ulang.',
        ]);

        Sanctum::actingAs($treasurer);

        $this->getJson('/api/transactions/audit-notifications')
            ->assertOk()
            ->assertJsonPath('0.id', $pendingTransaction['id'])
            ->assertJsonPath('0.status', 'flagged')
            ->assertJsonPath('0.audit_note', 'Nominal perlu dicek ulang.');

        $this->actingAs($treasurer)
            ->getJson(route('notifications.audit-transactions'))
            ->assertOk()
            ->assertJsonPath('0.id', $pendingTransaction['id'])
            ->assertJsonPath('0.status', 'flagged');

        $this->putJson('/api/transactions/' . $pendingTransaction['id'], [
            'category_id' => $category->id,
            'jumlah' => 110000,
            'tanggal_transaksi' => '2026-06-10 11:00:00',
            'catatan' => 'Sudah direvisi',
        ])->assertOk()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('needs_reaudit', true);

        Sanctum::actingAs($secretary);

        $this->getJson('/api/transactions/audit-notifications')
            ->assertOk()
            ->assertJsonPath('0.id', $pendingTransaction['id'])
            ->assertJsonPath('0.needs_reaudit', true);

        Sanctum::actingAs($owner);

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('summary.total_pemasukan', 0);

        Sanctum::actingAs($secretary);

        $this->patchJson('/api/transactions/' . $pendingTransaction['id'] . '/status', [
            'status' => 'verified',
        ])->assertOk();

        Sanctum::actingAs($owner);

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('summary.total_pemasukan', 110000)
            ->assertJsonPath('summary.saldo_real', 110000);
    }

    public function test_owner_invites_staff_and_staff_creates_password_before_entering_dashboard(): void
    {
        Mail::fake();

        [$owner, $business] = $this->createBusinessTeam();

        $response = $this->actingAs($owner)->postJson('/anggota', [
            'email' => 'staff@example.com',
            'role' => 'bendahara',
        ]);

        $response->assertOk();

        $staff = User::where('email', 'staff@example.com')->firstOrFail();
        $member = BusinessMember::where('business_id', $business->id)
            ->where('user_id', $staff->id)
            ->firstOrFail();

        $this->assertSame('pending', $member->status);
        $this->assertNotNull($member->invite_token);

        $this->get(route('members.accept', $member->invite_token))
            ->assertOk()
            ->assertSee('Buat Password')
            ->assertSee('staff@example.com');

        $this->post(route('members.password.store', $member->invite_token), [
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ])
            ->assertOk()
            ->assertSee('Sedang masuk ke Dashboard')
            ->assertSee('localStorage.setItem', false);

        $this->assertAuthenticatedAs($staff->fresh());
        $this->assertTrue(Hash::check('Password1', $staff->fresh()->password));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $staff->id,
            'name' => 'invitation-login',
        ]);
        $this->assertDatabaseHas('business_members', [
            'id' => $member->id,
            'status' => 'accepted',
            'role' => 'bendahara',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'role' => 'bendahara',
        ]);
    }

    public function test_owner_can_delete_staff_member_from_owned_business(): void
    {
        [$owner, $business, $secretary] = $this->createBusinessTeam();

        $member = BusinessMember::where('business_id', $business->id)
            ->where('user_id', $secretary->id)
            ->firstOrFail();
        $secretary->createToken('staff-session');

        $this->actingAs($owner)
            ->deleteJson(route('anggota.destroy', $member))
            ->assertOk()
            ->assertJsonPath('message', 'Anggota berhasil dihapus.');

        $this->assertDatabaseMissing('business_members', [
            'id' => $member->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $secretary->id,
        ]);
    }

    public function test_staff_without_active_membership_is_logged_out_from_web_pages(): void
    {
        [$owner, $business, $secretary] = $this->createBusinessTeam();

        BusinessMember::where('business_id', $business->id)
            ->where('user_id', $secretary->id)
            ->delete();

        $this->actingAs($secretary)
            ->get(route('buku-kas'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_owner_cannot_delete_staff_member_from_another_business(): void
    {
        [$owner] = $this->createBusinessTeam();
        [$otherOwner, $otherBusiness, $otherSecretary] = $this->createBusinessTeam();

        $otherMember = BusinessMember::where('business_id', $otherBusiness->id)
            ->where('user_id', $otherSecretary->id)
            ->firstOrFail();

        $this->actingAs($owner)
            ->deleteJson(route('anggota.destroy', $otherMember))
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses ditolak.');

        $this->assertDatabaseHas('business_members', [
            'id' => $otherMember->id,
            'business_id' => $otherBusiness->id,
        ]);
    }

    /**
     * @return array{0: User, 1: Business, 2?: User, 3?: User}
     */
    private function createBusinessTeam(): array
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $business = Business::create([
            'user_id' => $owner->id,
            'nama_usaha' => 'UEMKA Test',
        ]);

        $secretary = User::factory()->create([
            'role' => 'sekretaris',
            'email_verified_at' => now(),
        ]);

        $treasurer = User::factory()->create([
            'role' => 'bendahara',
            'email_verified_at' => now(),
        ]);

        foreach ([[$secretary, 'sekretaris'], [$treasurer, 'bendahara']] as [$user, $role]) {
            BusinessMember::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'accepted',
                'invite_token' => null,
                'invited_email' => $user->email,
                'accepted_at' => now(),
            ]);
        }

        return [$owner, $business, $secretary, $treasurer];
    }
}
