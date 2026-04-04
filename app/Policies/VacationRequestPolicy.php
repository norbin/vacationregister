<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VacationRequest;

class VacationRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VacationRequest $vacationRequest): bool
    {
        return $user->id === $vacationRequest->user_id || $user->is_manager || $user->isAdmin() || $user->isManager();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VacationRequest $vacationRequest): bool
    {
        return in_array($vacationRequest->status, ['pending', 'approved']) && $user->id === $vacationRequest->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VacationRequest $vacationRequest): bool
    {
        return $vacationRequest->status === 'pending' && $user->id === $vacationRequest->user_id;
    }

    public function approve(User $user, VacationRequest $vacationRequest): bool
    {
        if ($vacationRequest->status !== 'pending') {
            return false;
        }

        if ($user->id === $vacationRequest->user_id && ($user->isManager() || $user->is_manager)) {
            return true;
        }

        if ($user->id === $vacationRequest->user_id) {
            return false;
        }

        if ($user->isManager() || $user->is_manager) {
            return ! $vacationRequest->user->isManager() && ! $vacationRequest->user->is_manager;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VacationRequest $vacationRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VacationRequest $vacationRequest): bool
    {
        return false;
    }
}
