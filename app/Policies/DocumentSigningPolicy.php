<?php

namespace App\Policies;

use App\Models\DocumentSigner;
use App\Models\DocumentSigningRequest;
use App\Models\User;

class DocumentSigningPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array((int) $user->role_id, [
            config('document_signing.roles.admin'),
            config('document_signing.roles.secretary'),
            config('document_signing.roles.member'),
            config('document_signing.roles.presiding_officer'),
        ], true);
    }

    public function view(
        User $user,
        DocumentSigningRequest $request
    ): bool {
        if ($user->isAdministrator()) {
            return true;
        }

        return (int) $request->initiated_by === (int) $user->id
            || $request->signers()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function initiate(User $user): bool
    {
        return $user->isAdministrator()
            || $user->isSecretary();
    }

    public function sign(
        User $user,
        DocumentSigningRequest $request
    ): bool {
        if (! in_array($request->status, [
            'pending',
            'partially_signed',
        ], true)) {
            return false;
        }

        $nextSigner = $request->signers()
            ->where('status', 'pending')
            ->orderBy('signing_order')
            ->first();

        return $nextSigner
            && (int) $nextSigner->user_id === (int) $user->id;
    }

    public function reject(
        User $user,
        DocumentSigningRequest $request
    ): bool {
        return $this->sign($user, $request);
    }

    public function revoke(
        User $user,
        DocumentSigningRequest $request
    ): bool {
        return in_array($request->status, [
            'draft',
            'pending',
            'partially_signed',
        ], true) && (
            $user->isAdministrator()
            || (int) $request->initiated_by === (int) $user->id
        );
    }

    public function signCommitteeReport(
        User $user,
        int $committeeId
    ): bool {
        if (! $user->isCouncilMember()) {
            return false;
        }

        if (! $user->member_id) {
            return false;
        }

        return \App\Models\CommitteeMember::query()
            ->where('standing_comittee_id', $committeeId)
            ->where('member_id', $user->member_id)
            ->where('isActive', true)
            ->where('is_chairperson', true)
            ->whereHas('committee', function ($query) {
                $query->where('isActive', true);
            })
            ->exists();
    }

}
