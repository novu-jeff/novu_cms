<?php

namespace App\Services\DocumentSigning;

use InvalidArgumentException;

class DocumentTypeRegistry
{
    
    protected array $types = [
        'voting_results' => [
            'label' => 'Official Voting Results',
            'disk' => 'private',
            'signers' => [
                ['role' => 'Secretary', 'key' => 'secretary'],
                ['role' => 'Presiding Officer', 'key' => 'presiding_officer'],
            ],
        ],

        'committee_report' => [
            'label' => 'Individual Committee Report',
            'disk' => 'private',
            'signers' => [
                ['role' => 'Committee Chairperson', 'key' => 'committee_chairperson'],
                ['role' => 'Presiding Officer', 'key' => 'presiding_officer'],
            ],
        ],

        'consolidated_committee_report' => [
            'label' => 'Consolidated Committee Report',
            'disk' => 'private',
            'signers' => [
                ['role' => 'Committee Chairperson', 'key' => 'committee_chairperson'],
                ['role' => 'Presiding Officer', 'key' => 'presiding_officer'],
            ],
        ],

        'meeting_minutes' => [
            'label' => 'Official Meeting Minutes',
            'disk' => 'private',
            'signers' => [
                ['role' => 'Secretary', 'key' => 'secretary'],
                ['role' => 'Presiding Officer', 'key' => 'presiding_officer'],
            ],
        ],
    ];


    public function get(string $type): array
    {
        if (! isset($this->types[$type])) {
            throw new InvalidArgumentException(
                'Unsupported document type.'
            );
        }

        return $this->types[$type];
    }

    public function all(): array
    {
        return $this->types;
    }

    public function exists(string $type): bool
    {
        return isset($this->types[$type]);
    }
}
