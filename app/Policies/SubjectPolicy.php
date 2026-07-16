<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Subject $subject = null): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->role === 'admin';
    }
}