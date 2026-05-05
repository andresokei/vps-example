<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $users = User::query()
            ->with('roles:id,name')
            ->withCount(['grupos', 'tests', 'asignacionesTest'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 403);
        abort_if($user->hasRole('admin'), 403);

        $user->loadCount(['grupos', 'tests', 'asignacionesTest']);

        $hasActivity = $user->grupos_count > 0
            || $user->tests_count > 0
            || $user->asignaciones_test_count > 0;

        if ($hasActivity) {
            return back()->with('error', __('This user has activity and cannot be deleted from the quick admin cleanup.'));
        }

        $deletedEmail = $user->email;

        $user->syncRoles([]);
        $user->delete();

        return back()->with('message', __('User :email deleted.', ['email' => $deletedEmail]));
    }
}
