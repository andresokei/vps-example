<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AsignacionTest;
use App\Models\Grupo;
use App\Models\Respuesta;
use App\Models\Test;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $since7Days = now()->subDays(7);
        $since30Days = now()->subDays(30);

        $assignmentStatusCounts = AsignacionTest::query()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $recentUsers = User::query()
            ->with('roles:id,name')
            ->withCount(['grupos', 'tests', 'asignacionesTest'])
            ->latest()
            ->limit(8)
            ->get();

        $recentAssignments = AsignacionTest::query()
            ->with(['profesor:id,name,email', 'grupo:id,nombre_grupo', 'test:id,nombre_test'])
            ->withCount('respuestas')
            ->latest()
            ->limit(8)
            ->get();

        $activeProfessors = User::query()
            ->role('profesor')
            ->withCount(['grupos', 'tests', 'asignacionesTest'])
            ->get()
            ->sortByDesc(fn (User $user) => $user->grupos_count + $user->tests_count + $user->asignaciones_test_count)
            ->take(6)
            ->values();

        $inactiveUsers = User::query()
            ->doesntHave('grupos')
            ->doesntHave('tests')
            ->doesntHave('asignacionesTest')
            ->where('created_at', '<=', now()->subDay())
            ->latest()
            ->limit(6)
            ->get();

        $timeline = collect()
            ->merge($recentUsers->map(fn (User $user) => [
                'type' => __('New user'),
                'icon' => 'bi-person-plus',
                'title' => $user->name,
                'detail' => $user->email,
                'created_at' => $user->created_at,
            ]))
            ->merge(Grupo::query()
                ->with('profesor:id,name,email')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Grupo $group) => [
                    'type' => __('Group created'),
                    'icon' => 'bi-people',
                    'title' => $group->nombre_grupo,
                    'detail' => $group->profesor?->name ?? __('Unknown professor'),
                    'created_at' => $group->created_at,
                ]))
            ->merge(Test::query()
                ->with('profesor:id,name,email')
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Test $test) => [
                    'type' => __('Test created'),
                    'icon' => 'bi-file-earmark-check',
                    'title' => $test->nombre_test,
                    'detail' => $test->profesor?->name ?? __('Unknown professor'),
                    'created_at' => $test->created_at,
                ]))
            ->merge($recentAssignments->map(fn (AsignacionTest $assignment) => [
                'type' => __('Test assigned'),
                'icon' => 'bi-clipboard-plus',
                'title' => $assignment->test?->nombre_test ?? __('Untitled test'),
                'detail' => ($assignment->grupo?->nombre_grupo ?? __('Unknown group')).' / '.($assignment->profesor?->name ?? __('Unknown professor')),
                'created_at' => $assignment->created_at,
            ]))
            ->merge(Respuesta::query()
                ->with(['asignacion.profesor:id,name,email', 'asignacion.test:id,nombre_test'])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Respuesta $response) => [
                    'type' => __('Response submitted'),
                    'icon' => 'bi-chat-square-text',
                    'title' => $response->asignacion?->test?->nombre_test ?? __('Unknown test'),
                    'detail' => $response->asignacion?->profesor?->name ?? __('Unknown professor'),
                    'created_at' => $response->created_at,
                ]))
            ->filter(fn (array $event) => $event['created_at'])
            ->sortByDesc('created_at')
            ->take(12)
            ->values();

        return view('admin.dashboard', [
            'totals' => [
                'users' => User::count(),
                'professors' => User::role('profesor')->count(),
                'newUsers7Days' => User::where('created_at', '>=', $since7Days)->count(),
                'newUsers30Days' => User::where('created_at', '>=', $since30Days)->count(),
                'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
                'groups' => Grupo::count(),
                'tests' => Test::count(),
                'assignments' => AsignacionTest::count(),
                'responses' => Respuesta::count(),
                'responses7Days' => Respuesta::where('created_at', '>=', $since7Days)->count(),
            ],
            'assignmentStatusCounts' => $assignmentStatusCounts,
            'recentUsers' => $recentUsers,
            'recentAssignments' => $recentAssignments,
            'activeProfessors' => $activeProfessors,
            'inactiveUsers' => $inactiveUsers,
            'timeline' => $timeline,
        ]);
    }
}
