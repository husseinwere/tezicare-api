<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('admin', $roles);
        });

        Gate::define('reception', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('reception', $roles);
        });

        Gate::define('cashier', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('cashier', $roles);
        });

        Gate::define('nurse', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('nurse', $roles);
        });

        Gate::define('lab', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('lab', $roles);
        });

        Gate::define('radiology', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('radiology', $roles);
        });

        Gate::define('doctor', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('doctor', $roles);
        });

        Gate::define('pharmacy', function ($user) {
            $roles = explode(',', $user->roles);
            return in_array('pharmacy', $roles);
        });
    }
}
