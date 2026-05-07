<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $validStatuses = array_keys(UserFeedback::statuses());

        $feedback = UserFeedback::query()
            ->with(['user:id,name,email', 'reviewer:id,name,email'])
            ->when(in_array($status, $validStatuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.feedback.index', [
            'feedback' => $feedback,
            'statuses' => UserFeedback::statuses(),
            'categories' => UserFeedback::categories(),
            'activeStatus' => in_array($status, $validStatuses, true) ? $status : null,
            'statusCounts' => UserFeedback::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, UserFeedback $feedback): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(UserFeedback::statuses()))],
        ]);

        $feedback->forceFill([
            'status' => $validated['status'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return back()->with('status', __('Feedback status updated.'));
    }
}
