<?php

namespace App\Http\Middleware;

use App\Models\VacationRequest;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    ...$request->user()->toArray(),
                    'all_teams' => $request->user()->allTeams()->values(),
                    'current_team' => $request->user()->currentTeam,
                    'is_admin' => $request->user()->isAdmin(),
                    'is_manager' => $request->user()->isManager(),
                    'is_staff' => $request->user()->isStaff(),
                    'pending_vacation_requests_count' => $request->user()->vacationRequests()
                        ->where('status', 'pending')
                        ->count(),
                    'pending_approvals_count' => ($request->user()->isAdmin() || $request->user()->isManager() || $request->user()->is_manager) // TODO remove this logic put roles into ENUM?
                        ? VacationRequest::query()->where('status', 'pending')->count()
                        : 0,
                ] : null,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
