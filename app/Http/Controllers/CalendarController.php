<?php

namespace App\Http\Controllers;

use App\Models\VacationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $vacations = VacationRequest::with('user')
            ->where('status', 'approved')
            ->get()
            ->map(fn ($vacation) => [
                'id' => $vacation->id,
                'user_name' => $vacation->user->name,
                'substitute_name' => $vacation->substitute?->name ?? 'N/A',
                'start_date' => $vacation->start_date->toDateString(),
                'end_date' => $vacation->end_date->toDateString(),
                'status' => $vacation->status,
            ]);

        return Inertia::render('Vacations/Calendar', [
            'vacations' => $vacations,
        ]);
    }
}
