<?php

return [
    'roles' => [
        'admin' => 1,
        'secretary' => 2,
        'member' => 3,
        'presiding_officer' => 4,
    ],

    'statuses' => [
        'draft',
        'pending',
        'partially_signed',
        'fully_signed',
        'rejected',
        'revoked',
    ],

    'document_types' => [
        'voting_results' => 'Official Voting Results',
        'committee_report' => 'Committee Report',
        'meeting_minutes' => 'Meeting Minutes',
    ],

    'storage' => [
        'disk' => 'private',
        'documents' => 'signing/documents',
        'signatures' => 'signing/signatures',
        'final' => 'signing/final',
    ],
];
