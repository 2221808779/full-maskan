<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Image;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $tenant;
    protected User $technician;
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

        $this->owner = User::factory()->create([
            'user_type' => 'owner',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000002',
        ]);

        $this->tenant = User::factory()->create([
            'user_type' => 'tenant',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000003',
        ]);

        $this->technician = User::factory()->create([
            'user_type' => 'technician',
            'phone_verified_at' => now(),
            'status' => 'active',
            'phone' => '0910000004',
        ]);

        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'available',
        ]);
    }

    // ──────────────── FIX 1: ReviewController@show (removed 'booking' from load) ────────────────

    public function test_review_show_does_not_crash(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'stars' => 4,
            'comment' => 'Good place',
        ]);

        $response = $this->actingAs($this->tenant)
            ->getJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
    }

    // ──────────────── FIX 2: Payment $fillable includes user_id ────────────────

    public function test_payment_creation_with_user_id(): void
    {
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->tenant->id,
            'status' => 'completed',
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $this->tenant->id,
            'amount' => 500.00,
            'payment_type' => 'bank_transfer',
            'status' => 'completed',
        ]);

        $this->assertNotNull($payment->id);
        $this->assertEquals($this->tenant->id, $payment->user_id);
        $this->assertEquals(500.00, $payment->amount);
    }

    // ──────────────── FIX 3: AdminController API uses property_type filter ────────────────

    public function test_admin_properties_filter_by_type(): void
    {
        Property::factory()->create([
            'owner_id' => $this->owner->id,
            'property_type' => 'فيلا',
            'status' => 'available',
        ]);
        Property::factory()->create([
            'owner_id' => $this->owner->id,
            'property_type' => 'شقة',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/properties?type=فيلا');

        $response->assertStatus(200);
        $properties = $response->json('data');
        foreach ($properties as $p) {
            $this->assertEquals('فيلا', $p['property_type']);
        }
    }

    // ──────────────── FIX 4: WebBookingController@index owner filter ────────────────

    public function test_owner_booking_list_shows_only_own_properties(): void
    {
        $otherOwner = User::factory()->create(['user_type' => 'owner']);
        $otherProperty = Property::factory()->create([
            'owner_id' => $otherOwner->id,
            'status' => 'available',
        ]);

        Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);
        Booking::factory()->create([
            'property_id' => $otherProperty->id,
            'user_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('bookings.index'));

        $response->assertStatus(200);
        $response->assertViewHas('bookings');
        $bookings = $response->viewData('bookings');
        $this->assertEquals(1, $bookings->total());
        foreach ($bookings as $b) {
            $this->assertEquals($this->owner->id, $b->property->owner_id);
        }
    }

    // ──────────────── FIX 5: properties_review.blade.php uses asset($image->image_path) ────────────────

    public function test_admin_property_review_page_renders(): void
    {
        $property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);
        Image::create([
            'property_id' => $property->id,
            'image_path' => 'properties/test.webp',
            'added_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.review', $property));

        $response->assertStatus(200);
    }

    // ──────────────── FIX 6: WebPropertyController@store persists lat/lng/area ────────────────

    public function test_web_property_store_persists_lat_lng_area(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('properties.store'), [
                'title' => 'Test Property with Coordinates',
                'description' => 'Test description',
                'type' => 'villa',
                'price_per_night' => 1000,
                'rooms_count' => 3,
                'bathrooms_count' => 2,
                'city' => 'طرابلس',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'area_sqm' => 350,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $property = Property::where('title', 'Test Property with Coordinates')->first();
        $this->assertNotNull($property);
        $this->assertEquals(24.7136, $property->latitude);
        $this->assertEquals(46.6753, $property->longitude);
        $this->assertEquals(350, $property->area);
    }

    // ──────────────── FIX 8: Removed non-functional fields (price_per_month, deposit, amenities) ────────────────
    // Test that creating a property without these fields succeeds

    public function test_web_property_store_without_removed_fields_succeeds(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('properties.store'), [
                'title' => 'Minimal Property',
                'description' => 'Test',
                'type' => 'apartment',
                'price_per_night' => 500,
                'rooms_count' => 2,
                'bathrooms_count' => 1,
                'city' => 'بنغازي',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $property = Property::where('title', 'Minimal Property')->first();
        $this->assertNotNull($property);
        $this->assertNotNull($property->id);
    }

    // ──────────────── PROPERTY APPROVAL FLOW ────────────────

    public function test_web_property_create_forces_pending_status(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('properties.store'), [
                'title' => 'New Pending Property',
                'description' => 'Test',
                'type' => 'villa',
                'price_per_night' => 1500,
                'rooms_count' => 4,
                'bathrooms_count' => 3,
                'city' => 'طرابلس',
            ]);

        $response->assertSessionHasNoErrors();
        $property = Property::where('title', 'New Pending Property')->first();
        $this->assertEquals('pending', $property->status);
    }

    public function test_api_property_store_forces_pending_status(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/api/properties', [
                'title' => 'API Property',
                'description' => 'Test',
                'property_type' => 'villa',
                'price' => 2000,
                'rooms_count' => 3,
                'bathrooms_count' => 2,
                'location' => 'طرابلس',
            ]);

        $response->assertStatus(201);
        $property = Property::where('title', 'API Property')->first();
        $this->assertEquals('pending', $property->status);
    }

    public function test_admin_approve_property(): void
    {
        $property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.approve', $property));

        $response->assertSessionHasNoErrors();
        $this->assertEquals('available', $property->fresh()->status);
    }

    public function test_admin_reject_property(): void
    {
        $property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.properties.reject', $property), [
                'reason' => 'Missing documents',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('unavailable', $property->fresh()->status);
    }

    public function test_admin_pending_properties_page(): void
    {
        Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.pending'));

        $response->assertStatus(200);
        $response->assertViewHas('properties');
    }

    public function test_admin_api_approve_property(): void
    {
        $property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/properties/{$property->id}/approve");

        $response->assertStatus(200);
        $this->assertEquals('available', $property->fresh()->status);
    }

    public function test_admin_api_reject_property(): void
    {
        $property = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/properties/{$property->id}/reject");

        $response->assertStatus(200);
        $this->assertEquals('unavailable', $property->fresh()->status);
    }

    // ──────────────── BOOKING FLOW ────────────────

    public function test_complete_booking_flow(): void
    {
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->tenant->id,
            'status' => 'pending',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(5),
        ]);

        $this->actingAs($this->owner)
            ->get(route('bookings.confirm', $booking));
        $this->assertEquals('confirmed', $booking->fresh()->status);

        $this->actingAs($this->owner)
            ->get(route('bookings.checkin', $booking));
        $this->assertEquals('in_progress', $booking->fresh()->status);

        $this->actingAs($this->owner)
            ->get(route('bookings.complete', $booking));
        $this->assertEquals('completed', $booking->fresh()->status);
        $this->assertEquals('available', $this->property->fresh()->status);
    }

    // ──────────────── MAINTENANCE REQUEST FLOW ────────────────

    public function test_maintenance_request_full_flow(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson('/api/maintenance-requests', [
                'property_id' => $this->property->id,
                'description' => 'AC not working',
            ]);

        $response->assertStatus(201);
        $reqId = $response->json('maintenance_request.id');

        $this->actingAs($this->owner)
            ->putJson("/api/maintenance-requests/{$reqId}/assign", [
                'technician_id' => $this->technician->id,
            ]);

        $this->actingAs($this->technician)
            ->getJson('/api/technician/maintenance-requests')
            ->assertStatus(200);

        $this->actingAs($this->technician)
            ->putJson("/api/maintenance-requests/{$reqId}/reject", [
                'reason' => 'Too busy',
            ])
            ->assertStatus(200);
    }

    // ──────────────── COMPLAINTS FLOW ────────────────

    public function test_complaints_full_flow(): void
    {
        $response = $this->actingAs($this->tenant)
            ->postJson('/api/complaints', [
                'title' => 'Noise complaint',
                'description' => 'Loud construction',
            ]);

        $response->assertStatus(201);

        $this->actingAs($this->tenant)
            ->getJson('/api/complaints')
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('admin.complaints'))
            ->assertStatus(200);
    }

    // ──────────────── MESSAGES FLOW ────────────────

    public function test_messages_full_flow(): void
    {
        $msg = $this->actingAs($this->tenant)
            ->postJson('/api/messages', [
                'conversation_id' => $this->admin->id,
                'message' => 'Hello admin',
            ]);

        $msg->assertStatus(201);
        $msgId = $msg->json('data.id');

        $this->actingAs($this->tenant)
            ->putJson("/api/messages/{$msgId}", [
                'message' => 'Edited message',
            ])
            ->assertJsonPath('data.message_text', 'Edited message');

        $this->actingAs($this->tenant)
            ->getJson('/api/conversations')
            ->assertStatus(200);

        $this->actingAs($this->tenant)
            ->getJson("/api/conversations/{$this->admin->id}/messages")
            ->assertStatus(200);

        $this->actingAs($this->tenant)
            ->deleteJson("/api/messages/{$msgId}")
            ->assertStatus(200);
    }

    // ──────────────── UNAUTHENTICATED ACCESS ────────────────

    public function test_unauthenticated_access_blocked(): void
    {
        $this->getJson('/api/complaints')->assertStatus(401);
        $this->getJson('/api/conversations')->assertStatus(401);
        $this->postJson('/api/messages', [])->assertStatus(401);
        $this->postJson('/api/bookings', [])->assertStatus(401);
        $this->postJson('/api/maintenance-requests', [])->assertStatus(401);
    }

    // ──────────────── TENANT BOOKING LIST ────────────────

    public function test_tenant_sees_only_own_bookings(): void
    {
        $otherTenant = User::factory()->create(['user_type' => 'tenant']);
        $otherProperty = Property::factory()->create([
            'owner_id' => $this->owner->id,
            'status' => 'available',
        ]);

        Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->tenant->id,
        ]);
        Booking::factory()->create([
            'property_id' => $otherProperty->id,
            'user_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($this->tenant)
            ->get(route('bookings.index'));

        $response->assertStatus(200);
        $bookings = $response->viewData('bookings');
        $this->assertEquals(1, $bookings->total());
    }

    // ──────────────── ADMIN WEB PAGES ────────────────

    public function test_admin_dashboard_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    public function test_admin_properties_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.properties'))->assertStatus(200);
    }

    public function test_admin_bookings_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.bookings'))->assertStatus(200);
    }

    public function test_admin_users_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users'))->assertStatus(200);
    }

    public function test_admin_reports_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.reports'))->assertStatus(200);
    }

    public function test_admin_maintenance_page_loads(): void
    {
        $this->actingAs($this->admin)->get(route('admin.maintenance'))->assertStatus(200);
    }

    // ──────────────── PROPERTY CRUD (WEB) ────────────────

    public function test_web_property_show_loads(): void
    {
        $this->property->load('images', 'owner', 'bookings.user', 'reviews.user', 'activePrediction');

        $response = $this->actingAs($this->tenant)
            ->get(route('properties.show', $this->property));

        $response->assertStatus(200);
    }

    public function test_web_property_edit_loads(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('properties.edit', $this->property));

        $response->assertStatus(200);
    }

    public function test_web_property_update_works(): void
    {
        $response = $this->actingAs($this->owner)
            ->put(route('properties.update', $this->property), [
                'title' => 'Updated Title',
                'price_per_night' => 2000,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertEquals('Updated Title', $this->property->fresh()->title);
    }

    public function test_owner_can_delete_own_property(): void
    {
        $response = $this->actingAs($this->owner)
            ->delete(route('properties.destroy', $this->property));

        $response->assertSessionHasNoErrors();
        $this->assertNull($this->property->fresh());
    }

    // ──────────────── AI PREDICTION COMMAND ────────────────

    public function test_ai_predict_command_runs(): void
    {
        // Create some completed maintenance requests to have data
        $req = MaintenanceRequest::factory()->create([
            'property_id' => $this->property->id,
            'problem_description' => 'Test request',
            'status' => 'completed',
            'completed_at' => now()->subDays(30),
        ]);

        $this->artisan('ai:predict')
            ->assertExitCode(0);
    }
}
