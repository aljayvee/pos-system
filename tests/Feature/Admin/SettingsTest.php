<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class SettingsTest extends TestCase
{
    // Use DatabaseTransactions to rollback changes after test
    // Use RefreshDatabase to migrate schema for in-memory sqlite
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for testing
        $this->user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password123')
        ]);

        // Ensure Store 1 exists or mock it? 
        // Logic uses store_id 1 for global.

        // Mock Setup Complete
        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['store_id' => 1, 'key' => 'setup_complete'],
            ['value' => '1']
        );
    }

    public function test_settings_page_loads_without_error()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true]) // Bypass MPIN
            ->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.settings.index');
        // This implicitly checks for blade syntax errors as well during rendering
    }

    public function test_settings_update_works()
    {
        $newData = [
            'store_name' => 'Test Store Updated',
            'store_address' => '123 Test St',
            'store_contact' => '09123456789',
            'receipt_footer' => 'Test Footer',
            'enable_tithes' => '1',
            // Encrypted fields
            'store_tin' => '123-456-789-000',
            'business_permit' => 'BP-2025-001'
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->post(route('settings.update'), $newData);

        $response->assertSessionHas('success');
        $response->assertStatus(302);

        // Verify DB
        $this->assertDatabaseHas('settings', [
            'key' => 'store_name',
            'value' => 'Test Store Updated'
        ]);

        // Verify Encryption
        $tinSetting = Setting::where('key', 'store_tin')->first();
        $this->assertNotEquals('123-456-789-000', $tinSetting->value);
        $this->assertEquals('123-456-789-000', Crypt::decryptString($tinSetting->value));
    }

    public function test_reveal_credentials()
    {
        // Setup existing encrypted setting
        $tin = '999-888-777';
        Setting::updateOrCreate(
            ['key' => 'store_tin', 'store_id' => $this->user->store_id ?? 1],
            ['value' => Crypt::encryptString($tin)]
        );

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->postJson(route('settings.reveal'), [
                'password' => 'password123',
                'key' => 'store_tin'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'value' => $tin]);

        // Verify Log
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Security Access',
            'user_id' => $this->user->id
        ]);
    }

    public function test_verify_disable_bir()
    {
        // Setup
        $tin = '111-222-333';
        $permit = 'BP-TEST-001';
        $storeId = 1; // Assuming store 1

        Setting::updateOrCreate(['key' => 'store_tin', 'store_id' => $storeId], ['value' => Crypt::encryptString($tin)]);
        Setting::updateOrCreate(['key' => 'business_permit', 'store_id' => $storeId], ['value' => Crypt::encryptString($permit)]);

        $response = $this->actingAs($this->user)
            ->withSession(['mpin_verified' => true])
            ->postJson(route('settings.verify_disable_bir'), [
                'password' => 'password123',
                'tin' => $tin,
                'permit' => $permit
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
