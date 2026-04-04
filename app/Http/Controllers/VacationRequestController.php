<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VacationRequest;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VacationRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $myRequests = VacationRequest::with('substitute')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $users = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Vacations/Index', [
            'myRequests' => $myRequests,
            'vacationDaysRemaining' => $user->vacationDaysRemaining(),
            'vacationDaysYearly' => $user->vacation_days_yearly,
            'availableSubstitutes' => $users,
        ]);
    }

    public function approvals(Request $request): Response
    {
        $user = $request->user();

        if (! ($user->is_manager || $user->isManager())) {
            abort(403);
        }

        $requests = VacationRequest::with(['user', 'substitute'])
            ->latest()
            ->get();

        return Inertia::render('Vacations/Approvals', [
            'requests' => $requests,
        ]);
    }

    public function store(Request $request, HolidayService $holidayService): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'substitute_id' => 'nullable|exists:users,id',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $totalDays = $holidayService->getWorkingDays($start, $end);

        if ($totalDays <= 0) {
            return back()->withErrors(['end_date' => 'The vacation must be at least 1 working day.']);
        }

        $user = $request->user();

        if (isset($validated['substitute_id']) && $validated['substitute_id'] == $user->id) {
            return back()->withErrors(['substitute_id' => 'You cannot be your own substitute.']);
        }

        if ($user->vacationDaysRemaining() < $totalDays) {
            return back()->withErrors(['start_date' => 'You do not have enough vacation days left.']);
        }

        $user->vacationRequests()->create([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'substitute_id' => $validated['substitute_id'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->back()->banner('Vacation request submitted successfully.');
    }

    public function update(Request $request, VacationRequest $vacationRequest): RedirectResponse
    {
        Gate::authorize('update', $vacationRequest);

        $validated = $request->validate([
            'substitute_id' => 'nullable|exists:users,id',
        ]);

        if (isset($validated['substitute_id']) && $validated['substitute_id'] == $request->user()->id) {
            return back()->withErrors(['substitute_id' => 'You cannot be your own substitute.']);
        }

        $vacationRequest->update([
            'substitute_id' => $validated['substitute_id'] ?? null,
        ]);

        return redirect()->back()->banner('Substitute person updated successfully.');
    }

    public function approve(Request $request, VacationRequest $vacationRequest): RedirectResponse
    {
        Gate::authorize('approve', $vacationRequest);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_comment' => 'nullable|string|max:1000',
        ]);

        $vacationRequest->update([
            'status' => $validated['status'],
            'manager_comment' => $validated['manager_comment'],
            'approved_by_id' => $request->user()->id,
        ]);

        $pendingApprovals = VacationRequest::where('status', 'pending')->exists();

        if ($pendingApprovals) {
            return back()->banner('Vacation request status updated.');
        }

        return redirect()->route('vacations.calendar')->banner('Vacation request status updated.');
    }

    public function destroy(VacationRequest $vacationRequest): RedirectResponse
    {
        Gate::authorize('delete', $vacationRequest);

        $vacationRequest->delete();

        return redirect()->route('vacations.index')->banner('Vacation request cancelled.');
    }
}
