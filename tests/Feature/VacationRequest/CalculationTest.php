<?php

use App\Models\User;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Working Days & Holidays
|--------------------------------------------------------------------------
*/

test('it calculates vacation days excluding weekends and Hungarian holidays', function () {
    $user = User::factory()->withPersonalTeam()->create([
        'vacation_days_yearly' => 25,
    ]);

    // May 1st is a public holiday in Hungary (Labour Day)
    // In 2026, May 1st is a Friday.
    // April 30th (Thursday) to May 4th (Monday)
    // April 30 (Thu) - Working
    // May 1 (Fri) - Holiday
    // May 2 (Sat) - Weekend
    // May 3 (Sun) - Weekend
    // May 4 (Mon) - Working
    // Total working days: 2 (April 30, May 4)
    $startDate = '2026-04-30';
    $endDate = '2026-05-04';

    $this->actingAs($user)->post(route('vacations.store'), [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'reason' => 'Holiday test',
    ]);

    $this->assertDatabaseHas('vacation_requests', [
        'user_id' => $user->id,
        'start_date' => $startDate.' 00:00:00',
        'end_date' => $endDate.' 00:00:00',
        'total_days' => 2,
    ]);
});

test('holiday service correctly identifies Hungarian holidays', function () {
    $service = new HolidayService;

    // August 20 is State Foundation Day in Hungary
    $august20 = Carbon::parse('2026-08-20');
    expect($service->isHoliday($august20))->toBeTrue();

    // August 19 is not a holiday
    $august19 = Carbon::parse('2026-08-19');
    expect($service->isHoliday($august19))->toBeFalse();
});
