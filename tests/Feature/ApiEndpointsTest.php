<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected User $technician;
    protected User $tenant;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000001',
        ]);

        $this->user = User::factory()->create([
            'user_type' => 'owner',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000002',
        ]);

        $this->technician = User::factory()->create([
            'user_type' => 'technician',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000003',
        ]);

        $this->tenant = User::factory()->create([
            'user_type' => 'tenant',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000004',
        ]);

        $this->property = Property::factory()->create([
            'owner_id' => $this->user->id,
            'status' => 'available',
        ]);
    }

    // ── Auth ──

    public function test_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'tenant',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'requires_otp']]);
    }

    public function test_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'phone' => $this->tenant->phone,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user']);
    }

    public function test_login_wrong_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'phone' => $this->tenant->phone,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'full_name', 'user_type']]);
    }

    public function test_update_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/auth/profile', [
                'full_name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.full_name', 'Updated Name');
    }

    public function test_logout(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    // ── Properties ──

    public function test_list_properties(): void
    {
        Cache::flush();
        $response = $this->getJson('/api/properties');
        $response->assertStatus(200);
    }

    public function test_show_property(): void
    {
        $response = $this->getJson("/api/properties/{$this->property->id}");
        $response->assertStatus(200);
    }

    public function test_property_availability(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/properties/{$this->property->id}/availability");

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'availability' => ['booked_ranges', 'blackout_dates']]);
    }

    public function test_property_blackout_dates(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/properties/{$this->property->id}/blackout-dates");

        $response->assertStatus(200);
    }

    // ── Complaints ──

    public function test_create_complaint(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/complaints', [
                'title' => 'Test complaint',
                'description' => 'This is a test complaint description',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_list_complaints(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/complaints', [
                'title' => 'Complaint 1',
                'description' => 'Description 1',
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/complaints');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'complaints']);
    }

    public function test_admin_can_see_all_complaints(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/complaints', [
                'title' => 'User complaint',
                'description' => 'User desc',
            ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/complaints');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    // ── Messages / Conversations ──

    public function test_send_message(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Hello admin!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'conversation_id', 'message_text']]);
    }

    public function test_list_conversations(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'First message',
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_list_messages_in_conversation(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Hi admin',
            ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/conversations/{$this->admin->id}/messages");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_edit_message(): void
    {
        $msg = $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Original text',
            ]);
        $msgId = $msg->json('data.id');

        $response = $this->actingAs($this->user)
            ->putJson("/api/messages/{$msgId}", [
                'message' => 'Edited text',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.message_text', 'Edited text');
    }

    public function test_delete_message(): void
    {
        $msg = $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Delete me',
            ]);
        $msgId = $msg->json('data.id');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/messages/{$msgId}");

        $response->assertStatus(200);
    }

    public function test_delete_conversation(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Message',
            ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/conversations/{$this->admin->id}");

        $response->assertStatus(200);
    }

    public function test_mark_messages_as_read(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/messages', [
                'conversation_id' => $this->user->id,
                'message' => 'Reply from admin',
            ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/messages/{$this->admin->id}/read");

        $response->assertStatus(200);
    }

    // ── Auth extras ──

    public function test_deactivate_account(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/auth/deactivate');

        $response->assertStatus(200);
        $this->assertEquals('suspended', $this->user->fresh()->status);
    }

    public function test_delete_account(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/auth/delete');

        $response->assertStatus(200);
        $this->assertNull(User::find($this->user->id));
    }

    public function test_upload_photo(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->actingAs($this->user)
            ->post('/api/auth/profile/photo', [
                'profile_image' => $file,
            ]);

        $response->assertStatus(200);
    }

    // ── Maintenance ──

    public function test_create_maintenance_request(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/maintenance-requests', [
                'property_id' => $this->property->id,
                'description' => 'Broken pipe in kitchen',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'maintenance_request']);
    }

    public function test_reject_maintenance_request(): void
    {
        $request = $this->actingAs($this->user)
            ->postJson('/api/maintenance-requests', [
                'property_id' => $this->property->id,
                'description' => 'Broken pipe',
            ]);

        $reqId = $request->json('maintenance_request.id');

        $this->actingAs($this->user)
            ->putJson("/api/maintenance-requests/{$reqId}/assign", [
                'technician_id' => $this->technician->id,
            ]);

        $response = $this->actingAs($this->technician)
            ->putJson("/api/maintenance-requests/{$reqId}/reject", [
                'reason' => 'Too busy',
            ]);

        $response->assertStatus(200);
    }

    public function test_technician_requests(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/maintenance-requests', [
                'property_id' => $this->property->id,
                'description' => 'Broken pipe',
            ]);

        $response = $this->actingAs($this->technician)
            ->getJson('/api/technician/maintenance-requests');

        $response->assertStatus(200);
    }

    // ── Unauthenticated access ──

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/complaints');
        $response->assertStatus(401);

        $response = $this->getJson('/api/conversations');
        $response->assertStatus(401);

        $response = $this->postJson('/api/messages', []);
        $response->assertStatus(401);
    }
}
