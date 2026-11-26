<?php

namespace App\Services\DocumentSigning;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DocumentSignerResolver
{
    protected DocumentTypeRegistry $registry;

    public function __construct(DocumentTypeRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Resolve the official signers for a document.
     *
     * Signer identities are determined server-side.
     */
    public function resolve(
        string $documentType,
        ?int $committeeId = null
    ): array {
        if (! $this->registry->exists($documentType)) {
            throw ValidationException::withMessages([
                'document_type' => 'Invalid document signing type.',
            ]);
        }

        $type = $this->registry->get($documentType);
        $resolved = [];

        foreach ($type['signers'] as $signer) {
            $user = match ($signer['key']) {
                'secretary' => $this->resolveOfficeUser('secretary'),
                'presiding_officer' => $this->resolveOfficeUser('presiding_officer'),
                'committee_chairperson' => $this->resolveCommitteeChairperson($committeeId),
                default => null,
            };

            if (! $user) {
                throw ValidationException::withMessages([
                    'signers' => "Unable to resolve the assigned {$signer['role']} account.",
                ]);
            }

            $resolved[] = [
                'user_id' => $user->id,
                'role' => $signer['role'],
            ];
        }

        $userIds = array_column($resolved, 'user_id');

        if (count($userIds) !== count(array_unique($userIds))) {
            throw ValidationException::withMessages([
                'signers' => 'The signing sequence contains duplicate user accounts.',
            ]);
        }

        return $resolved;
    }

    /**
     * Resolve an office account and require exactly one matching user.
     */
    protected function resolveOfficeUser(string $role): User
    {
        $roleId = config("document_signing.roles.{$role}");

        if (! $roleId) {
            throw ValidationException::withMessages([
                'signers' => "The {$role} role is not configured.",
            ]);
        }

        $users = User::query()
            ->where('role_id', $roleId)
            ->get();

        if ($users->count() !== 1) {
            throw ValidationException::withMessages([
                'signers' => "Expected exactly one {$role} account, but found {$users->count()}.",
            ]);
        }

        return $users->first();
    }

    /**
     * Resolve the active chairperson assigned to the specified committee.
     */
    protected function resolveCommitteeChairperson(?int $committeeId): User
    {
        if (! $committeeId) {
            throw ValidationException::withMessages([
                'committee' => 'A committee must be specified for this document.',
            ]);
        }

        return app(DocumentSigningService::class)
            ->getCommitteeChairperson($committeeId);
    }
}