<?php

namespace App\Providers;

use App\Models\DocumentSigningRequest;
use App\Policies\DocumentSigningPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DocumentSigningRequest::class => DocumentSigningPolicy::class,
    ];

    /**
     * Register authentication and authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | Global Authorization
        |--------------------------------------------------------------------------
        |
        | Administrators can access all document-signing functions.
        | All other users must pass the corresponding policy checks.
        |
        */

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'isAdministrator') && $user->isAdministrator()) {
                return true;
            }

            return null;
        });

        /*
        |--------------------------------------------------------------------------
        | Document Signing Abilities
        |--------------------------------------------------------------------------
        */

        Gate::define('viewAny-document-signing', function ($user) {
            return $user->isSecretary()
                || $user->isPresidingOfficer()
                || $user->isCouncilMember();
        });

        Gate::define('initiate-document-signing', function ($user) {
            return $user->isSecretary();
        });

        Gate::define('sign-document', function (
            $user,
            DocumentSigningRequest $signingRequest
        ) {
            return $signingRequest->signers()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();
        });

        Gate::define('reject-document', function (
            $user,
            DocumentSigningRequest $signingRequest
        ) {
            return $signingRequest->signers()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();
        });

        Gate::define('revoke-document', function (
            $user,
            DocumentSigningRequest $signingRequest
        ) {
            return $signingRequest->initiated_by === $user->id
                && in_array($signingRequest->status, [
                    'draft',
                    'pending',
                    'partially_signed',
                ], true);
        });
    }
}
