<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Review;
use App\Models\Specialty;
use App\Models\User;
use App\Services\MaintenanceAIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * تحكم الصيانة (Web) — إدارة طلبات الصيانة وعرضها وتصنيفها عبر واجهة الويب
 */
class WebMaintenanceController extends Controller
{
    /**
     * Display a paginated list of maintenance requests for the authenticated user.
     * GET /maintenance
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $query = MaintenanceRequest::with('property', 'technician');

        if ($request->user()->user_type === 'tenant') {
            $query->where('tenant_id', auth()->id());
        } elseif ($request->user()->user_type === 'owner') {
            $query->whereIn('property_id', Property::where('owner_id', auth()->id())->pluck('id'));
        } elseif ($request->user()->user_type === 'technician') {
            $query->where('technician_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->paginate(15);

        return view('maintenance.index', compact('requests'));
    }

    /**
     * Show the form for creating a new maintenance request.
     * GET /maintenance/create
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        if (auth()->user()->user_type === 'owner') {
            abort(403);
        }

        $properties = Property::whereIn('id', Booking::where('user_id', auth()->id())
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->pluck('property_id'))
            ->get();

        return view('maintenance.create', compact('properties'));
    }

    /**
     * Store a new maintenance request with AI classification.
     * POST /maintenance
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\MaintenanceAIService  $ai
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, MaintenanceAIService $ai): RedirectResponse
    {
        if ($request->user()->user_type === 'owner') {
            abort(403);
        }

        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'problem_description' => 'required|string',
        ]);

        $aiResult = $ai->classify($validated['problem_description']);

        $priority = $this->inferPriority($validated['problem_description']);

        $maintenance = MaintenanceRequest::create([
            'property_id' => $validated['property_id'],
            'tenant_id' => $request->user()->id,
            'problem_description' => $validated['problem_description'],
            'ai_category' => $aiResult['category'],
            'ai_accuracy' => $aiResult['confidence'],
            'category' => $aiResult['category'],
            'category_id' => $aiResult['category_id'],
            'priority' => $priority,
            'status' => 'pending',
        ]);

        $property = Property::find($validated['property_id']);

        Notification::create([
            'user_id' => $property->owner_id,
            'title' => __('New maintenance request'),
            'content' => __('New maintenance request from tenant :tenant for property :property', ['tenant' => $request->user()->full_name, 'property' => $property->title]) . ($aiResult['category'] ? ' - ' . __('AI category: :category', ['category' => $aiResult['category']]) : ''),
        ]);

        return redirect()->route('maintenance.index')
            ->with('success', __('Maintenance request sent successfully'));
    }

    /**
     * Display the details of a maintenance request including technicians and reviews.
     * GET /maintenance/{maintenanceRequest}
     *
     * @param  \App\Models\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\View\View
     */
    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $maintenanceRequest->load('property', 'technician', 'tenant');

        $maintenanceRequest->load('property.activePrediction');

        $category = $maintenanceRequest->ai_category;
        $specialty = $category ? Specialty::where('name', $category)->first() : null;

        $technicians = User::where('user_type', 'technician')
            ->when($specialty, function ($q) use ($specialty) {
                $q->whereHas('technicianProfile.specializations', function ($sq) use ($specialty) {
                    $sq->where('specialization_id', $specialty->id);
                });
            })
            ->with('technicianProfile.specializations')
            ->get()
            ->sortByDesc(fn($u) => $u->technicianProfile?->avg_rating ?? 0);

        $review = $maintenanceRequest->technician_id
            ? Review::where('technician_id', $maintenanceRequest->technician_id)
                ->where('user_id', $maintenanceRequest->tenant_id)
                ->first()
            : null;

        return view('maintenance.show', compact('maintenanceRequest', 'technicians', 'category', 'review'));
    }

    /**
     * Assign a technician to a maintenance request (owner or admin only).
     * POST /maintenance/{maintenanceRequest}/assign
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assign(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $user = $request->user();
        $isOwner = $maintenanceRequest->property->owner_id === $user->id;
        if ($user->user_type !== 'admin' && !$isOwner) {
            return back()->with('error', __('Unauthorized action'));
        }

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $maintenanceRequest->update([
            'technician_id' => $validated['technician_id'],
            'status' => 'assigned',
        ]);

        $tech = User::find($validated['technician_id']);

        Notification::create([
            'user_id' => $validated['technician_id'],
            'title' => __('New maintenance task'),
            'content' => __('A maintenance task has been assigned to you for property :property', ['property' => $maintenanceRequest->property->title]),
        ]);

        Notification::create([
            'user_id' => $maintenanceRequest->tenant_id,
            'title' => __('Technician assigned'),
            'content' => __('A technician has been assigned for your maintenance request at property :property', ['property' => $maintenanceRequest->property->title]),
        ]);

        return back()->with('success', __('Technician assigned successfully'));
    }

    /**
     * Reject an assigned maintenance request (technician only).
     * POST /maintenance/{maintenanceRequest}/reject
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $user = $request->user();
        if ($maintenanceRequest->technician_id !== $user->id) {
            return back()->with('error', __('Unauthorized action'));
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $maintenanceRequest->update([
            'status' => 'pending',
            'technician_id' => null,
            'technician_notes' => $validated['reason'],
        ]);

        Notification::create([
            'user_id' => $maintenanceRequest->tenant_id,
            'title' => __('Maintenance request rejected'),
            'content' => __('The technician has rejected the maintenance request for :property. Reason: :reason A new technician will be assigned.', [
                'property' => $maintenanceRequest->property->title,
                'reason' => $validated['reason'],
            ]),
        ]);

        Notification::create([
            'user_id' => $maintenanceRequest->property->owner_id,
            'title' => __('Maintenance request rejected'),
            'content' => __('Technician :tech has rejected the maintenance request for :property. Reason: :reason', [
                'tech' => $user->full_name,
                'property' => $maintenanceRequest->property->title,
                'reason' => $validated['reason'],
            ]),
        ]);

        return back()->with('success', __('Maintenance request rejected'));
    }

    /**
     * Update the status of a maintenance request (all authorized roles).
     * POST /maintenance/{maintenanceRequest}/status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function status(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $user = $request->user();
        $isOwner = $maintenanceRequest->property->owner_id === $user->id;
        $isTenant = $maintenanceRequest->tenant_id === $user->id;
        $isTechnician = $maintenanceRequest->technician_id === $user->id;
        if ($user->user_type !== 'admin' && !$isOwner && !$isTenant && !$isTechnician) {
            return back()->with('error', __('Unauthorized action'));
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'technician_notes' => 'nullable|string',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'completed') {
            $data['completed_at'] = now();
            app(MaintenanceAIService::class)->predictNextMaintenance($maintenanceRequest->property_id, $maintenanceRequest->ai_category ?? 'other');
        }

        if ($validated['status'] === 'cancelled' && $maintenanceRequest->status === 'assigned') {
            $data['technician_id'] = null;
        }

        if (isset($validated['technician_notes'])) {
            $data['technician_notes'] = $validated['technician_notes'];
        }

        $maintenanceRequest->update($data);

        $statusLabels = [
            'pending' => __('Pending assignment'),
            'assigned' => __('Assigned'),
            'in_progress' => __('In progress'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];

        $displayLabel = $statusLabels[$data['status']] ?? $data['status'];

        Notification::create([
            'user_id' => $maintenanceRequest->tenant_id,
            'title' => __('Maintenance request status update'),
            'content' => __('Maintenance request status for property :property updated to: :status', ['property' => $maintenanceRequest->property->title, 'status' => $displayLabel]),
        ]);

        Notification::create([
            'user_id' => $maintenanceRequest->property->owner_id,
            'title' => __('Maintenance request status update'),
            'content' => __('Maintenance request status for property :property updated to: :status', ['property' => $maintenanceRequest->property->title, 'status' => $displayLabel]),
        ]);

        return back()->with('success', __('Maintenance request status updated'));
    }

    /**
     * Infer the priority level of a maintenance request from the description text.
     *
     * @param  string  $text
     * @return string
     */
    protected function inferPriority(string $text): string
    {
        $urgentKeywords = ['طوارئ', 'عاجل', 'طارئ', 'ضروري', 'مهم', 'حرق', 'تماس', 'تسرب', 'ماس كهربائي', 'ماس كهرباء', 'انقطاع', 'غرق', 'فيضان',
            'emergency', 'urgent', 'critical', 'important', 'fire', 'flood', 'gas leak', 'shock'];
        $highKeywords = ['كسر', 'انكسار', 'توقف', 'عطل', 'تلف', 'لا يعمل', 'خربان', 'عطلان', 'عطل', 'مشكلة',
            'broken', 'not working', 'damage', 'malfunction', 'defect'];

        $text = mb_strtolower($text);
        foreach ($urgentKeywords as $w) { if (str_contains($text, $w)) return 'urgent'; }
        foreach ($highKeywords as $w) { if (str_contains($text, $w)) return 'high'; }

        return 'medium';
    }

    /**
     * Rate a completed maintenance request (tenant only).
     * POST /maintenance/{maintenanceRequest}/rate
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MaintenanceRequest  $maintenanceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rate(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        if ($maintenanceRequest->tenant_id !== auth()->id() || $maintenanceRequest->status !== 'completed') {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'technician_id' => $maintenanceRequest->technician_id,
            'stars' => $validated['rating'],
        ]);

        Notification::create([
            'user_id' => $maintenanceRequest->technician_id,
            'title' => __('New rating'),
            'content' => __('Tenant rated your maintenance work :rating out of 5 stars', ['rating' => $validated['rating']]),
        ]);

        return back()->with('success', __('Rating submitted successfully'));
    }
}
