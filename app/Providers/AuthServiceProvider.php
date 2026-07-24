<?php

namespace App\Providers;

use App\Models\Subject;
use App\Policies\SubjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Subject::class => SubjectPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
