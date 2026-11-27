@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Notifications</h2>

    @forelse($notifications as $notification)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">
                    New Document for Review
                </h5>

                <p class="mb-1">
                    {{ $notification->data['document_title'] ?? 'New legislative document' }}
                </p>

                <p class="text-muted small">
                    {{ $notification->created_at->format('M d, Y h:i A') }}
                </p>

                <form
                    method="POST"
                    action="{{ route('secretary.notifications.read', $notification->id) }}"
                >
                    @csrf

                    <button class="btn btn-primary btn-sm">
                        Review Document
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="alert alert-light border">
            No notifications found.
        </div>
    @endforelse

    {{ $notifications->links() }}
</div>
@endsection