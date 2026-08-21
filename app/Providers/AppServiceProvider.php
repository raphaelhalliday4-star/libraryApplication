<?php

namespace App\Providers;
use App\Policies\AuthorPolicy;
use App\Policies\BookPolicy;
use App\Policies\BorrowingPolicy;
use App\Policies\CategoryPolicy;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Policies\FinePolicy;
use App\Policies\MemeberPolicy;
use App\Policies\PublisherPolicy;
use App\Policies\ReservationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, BookPolicy::class);
        Gate::policy(Role::class, AuthorPolicy::class);
        Gate::policy(Borrowing::class, BorrowingPolicy::class);
        Gate::policy(Fine::class, FinePolicy::class);
        Gate::policy(Role::class, CategoryPolicy::class);
        Gate::policy(Role::class, MemeberPolicy::class);
        Gate::policy(Role::class, PublisherPolicy::class);
        Gate::policy(Role::class, ReservationPolicy::class);
    }
}
