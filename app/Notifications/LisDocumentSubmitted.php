<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LisDocumentSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public array $document
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document['document_id'],
            'document_title' => $this->document['title'],
            'member_name' => $this->document['member_name'],
            'message' => 'A new legislative document has been submitted for review.',
            'document_url' => $this->document['document_url'],
            'source' => 'LIS',
        ];
    }
}