<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        if (! $request->user()->isAdmin() && ! $request->user()->isManager()) {
            abort(403);
        }

        return Inertia::render('Users/Index', [
            'users' => User::withExists(['vacationRequests as has_pending_requests' => function ($query) {
                $query->where('status', 'pending');
            }])->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'vacation_days_yearly' => $user->vacation_days_yearly,
                    'vacation_days_used' => $user->vacationDaysUsed(),
                    'vacation_days_remaining' => $user->vacationDaysRemaining(),
                    'is_admin' => $user->isAdmin(),
                    'is_manager' => $user->isManager(),
                    'has_pending_requests' => $user->has_pending_requests,
                ];
            }),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if (! $request->user()->isAdmin() && ! $request->user()->isManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'vacation_days_yearly' => 'required|integer|min:0|max:365',
            'is_admin' => 'sometimes|boolean',
            'is_manager' => 'sometimes|boolean',
        ]);

        if (isset($validated['vacation_days_yearly'])) {
            $user->update(['vacation_days_yearly' => $validated['vacation_days_yearly']]);
        }

        if (isset($validated['is_admin'])) {
            $adminTeam = Team::where('name', 'Admin')->first();
            if ($adminTeam) {
                if ($validated['is_admin']) {
                    $user->teams()->syncWithoutDetaching([$adminTeam->id => ['role' => 'admin']]);
                } else {
                    $user->teams()->detach($adminTeam->id);
                }
            }
        }

        if (isset($validated['is_manager'])) {
            $managerTeam = Team::where('name', 'Manager')->first();
            if ($managerTeam) {
                if ($validated['is_manager']) {
                    $user->teams()->syncWithoutDetaching([$managerTeam->id => ['role' => 'editor']]);
                } else {
                    $user->teams()->detach($managerTeam->id);
                }
            }
        }

        return redirect()->back()->with('status', 'User updated successfully.');
    }
}
