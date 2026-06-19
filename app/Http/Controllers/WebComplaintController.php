<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * تحكم الشكاوى (Web) — عرض وإرسال الشكاوى عبر واجهة الويب
 */
class WebComplaintController extends Controller
{
    /**
     * Display a paginated list of complaints for the user or all for admin.
     * GET /complaints
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $complaints = Message::where('type', 'complaint');

        if ($user->user_type === 'admin') {
            $complaints = $complaints->with('sender', 'responder');
        } else {
            $complaints = $complaints->where('sender_id', $user->id);
        }

        $complaints = $complaints->latest('sent_at')->paginate(5);
        return view('complaints.index', compact('complaints'));
    }

    /**
     * Show the form for submitting a new complaint.
     * GET /complaints/create
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('complaints.create');
    }

    /**
     * Store a newly submitted complaint.
     * POST /complaints
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message_text' => 'required|string|max:500',
        ]);

        $admin = User::where('user_type', 'admin')->first();

        Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $admin?->id,
            'message_text' => $validated['message_text'],
            'type' => 'complaint',
            'complaint_status' => 'pending',
            'sent_at' => now(),
        ]);

        return redirect()->route('complaints.index')->with('success', __('Complaint submitted successfully'));
    }

    /**
     * Display a single complaint's details.
     * GET /complaints/{complaint}
     *
     * @param  \App\Models\Message  $complaint
     * @return \Illuminate\View\View
     */
    public function show(Message $complaint): View
    {
        if ($complaint->type !== 'complaint') {
            abort(404);
        }
        $complaint->load('sender', 'responder');
        return view('complaints.show', compact('complaint'));
    }

    /**
     * Respond to a complaint with an admin reply (admin only).
     * POST /complaints/{complaint}/respond
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function respond(Request $request, Message $complaint): RedirectResponse
    {
        if ($request->user()->user_type !== 'admin') {
            return back()->with('error', __('Unauthorized action'));
        }

        if ($complaint->type !== 'complaint') {
            abort(404);
        }

        $validated = $request->validate([
            'admin_response' => 'required|string',
        ]);

        $complaint->update([
            'admin_response' => $validated['admin_response'],
            'complaint_status' => 'resolved',
            'responded_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        Notification::create([
            'user_id' => $complaint->sender_id,
            'title' => __('Complaint Response'),
            'content' => __('Your complaint has been responded to by the admin'),
            'type' => 'general',
            'action_url' => '/complaints/' . $complaint->id,
        ]);

        return redirect()->route('complaints.show', $complaint)->with('success', __('Response submitted'));
    }

    /**
     * Update the status of a complaint (admin only).
     * POST /complaints/{complaint}/status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $complaint
     * @return \Illuminate\Http\RedirectResponse
     */
    public function status(Request $request, Message $complaint): RedirectResponse
    {
        if ($request->user()->user_type !== 'admin') {
            return back()->with('error', __('Unauthorized action'));
        }

        if ($complaint->type !== 'complaint') {
            abort(404);
        }

        $validated = $request->validate([
            'complaint_status' => 'required|in:pending,in_review,resolved,dismissed',
        ]);

        $status = $validated['complaint_status'];
        $data = ['complaint_status' => $status];

        if ($status === 'resolved') {
            $data['resolved_at'] = now();
        }

        $complaint->update($data);

        $statusMessages = [
            'pending' => 'Your complaint is now pending review',
            'in_review' => 'Your complaint is now under review',
            'resolved' => 'Your complaint has been resolved',
            'dismissed' => 'Your complaint has been dismissed',
        ];

        Notification::create([
            'user_id' => $complaint->sender_id,
            'title' => __('Complaint Status Updated'),
            'content' => __($statusMessages[$status]),
            'type' => 'general',
            'action_url' => '/complaints/' . $complaint->id,
        ]);

        return redirect()->route('complaints.show', $complaint)->with('success', __('Complaint status updated'));
    }
}
