<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Product;
use App\Models\Comment;
use App\Models\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('Product-Protection', function (User $user, Product $product) {
            return $user->id === $product->user_id;
        });
        Gate::define('Comment-Protection', function (User $user, Comment $comment) {
            return $user->id === $comment->user_id;
        });
        Gate::define('Super-Admin-Protection', function (User $user) {
            return $user->role_id === 2;
        });
        Gate::define('Get-All-users', function (User $user) {
            if (
                $user->role_id == 2 ||
                Role::find(Auth::user()->role_id)->ability == 6 ||
                Role::find(Auth::user()->role_id)->ability == 2 ||
                Role::find(Auth::user()->role_id)->ability == 3
            ) {
                return true;
            }
            return false;
        });
        Gate::define('CUD-Categories', function (User $user) {
            if (
                $user->role_id == 2 ||
                Role::find(Auth::user()->role_id)->ability == 6 ||
                Role::find(Auth::user()->role_id)->ability == 7
            ) {
                return true;
            }
            return false;
        });
    }
}
