<?php

namespace App\Http\Controllers;

use App\Models\UserFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function create(): View
    {
        $recentFeedback = UserFeedback::query()
            ->whereBelongsTo(auth()->user())
            ->latest()
            ->limit(5)
            ->get();

        return view('feedback.create', [
            'categories' => UserFeedback::categories(),
            'statuses' => UserFeedback::statuses(),
            'recentFeedback' => $recentFeedback,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(UserFeedback::categories()))],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $request->user()->feedback()->create($validated);

        return redirect()
            ->route('feedback.create')
            ->with('status', __('Thanks, your feedback has been sent.'));
    }
}
