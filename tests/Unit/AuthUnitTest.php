<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthUnitTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $tenant;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        $this->owner = User::factory()->create([
            'user_type' => 'owner',
            'status'    => 'active',
        ]);

        $this->tenant = User::factory()->create([
            'user_type' => 'tenant',
            'status'    => 'active',
        ]);

        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status'   => 'available',
        ]);
    }

    // should block unauthenticated access to bookings
    public function test_should_block_unauthenticated_access_to_bookings()
    {
        $this->getJson('/api/bookings')
             ->assertStatus(401);
    }

    // should block unauthenticated access to complaints
    public function test_should_block_unauthenticated_access_to_complaints()
    {
        $this->getJson('/api/complaints')
             ->assertStatus(401);
    }

    // should allow tenant to send complaint
    public function test_should_allow_tenant_to_send_complaint()
    {
        $this->actingAs($this->tenant)
             ->postJson('/api/complaints', [
                 'title'       => 'شكوى اختبار',
                 'description' => 'وصف الشكوى',
             ])->assertStatus(201);
    }

    // should block adding property with empty fields
    public function test_should_block_adding_property_with_empty_fields()
    {
        $this->actingAs($this->owner)
             ->postJson('/api/properties', [
                 'title'       => '',
                 'description' => '',
             ])->assertStatus(422);
    }

    // should allow admin to access dashboard
    public function test_should_allow_admin_to_access_dashboard()
    {
        $this->actingAs($this->admin)
             ->get(route('admin.dashboard'))
             ->assertStatus(200);
    }
}