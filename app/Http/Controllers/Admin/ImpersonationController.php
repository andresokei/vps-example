<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin?->hasRole('admin'), 403);
        abort_if($user->is($admin), 403);
        abort_if($user->hasRole('admin'), 403);

        $request->session()->put('impersonator_id', $admin->id);
        $request->session()->put('impersonator_name', $admin->name);
        $request->session()->put('impersonated_user_id', $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('message', __('You are now viewing the application as :name in read-only mode.', ['name' => $user->name]));
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->pull('impersonator_id');
        $request->session()->forget(['impersonator_name', 'impersonated_user_id']);

        abort_unless($adminId, 403);

        $admin = User::findOrFail($adminId);

        abort_unless($admin->hasRole('admin'), 403);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.users.index')
            ->with('message', __('You are back in your admin account.'));
    }
}
