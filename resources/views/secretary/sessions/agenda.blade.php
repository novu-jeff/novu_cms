@extends('layouts.app')

@section('content')

<div class="container py-5">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h2 class="fw-semibold text-dark mb-1">Session Agenda</h2>
            <p class="text-muted mb-0">Manage Session Agenda documents in this module</p>
        </div>

        
    </div>


    <div class="card mb-3">
    <div class="card-body">
    
    <strong>Session:</strong> {{ $session->title }} <br>
    <strong>Date:</strong> {{ \Carbon\Carbon::parse($session->session_date)->format('M d, Y') }}
    
    </div>
    </div>

    <div class="mb-3 text-end">

        <!--<a href="{{ route('secretary.session.packet',$session->id) }}"
        class="btn btn-success">
        
        Generate Agenda Packet
        
        </a>-->

        
        <a href="{{ route('secretary.minutes.index', $session->id) }}"
            class="btn btn-primary">
             <i class="fa-solid fa-file-pen"></i>Encode Minutes
         </a>
        
    </div>

    <ul id="agendaLists" class="list-group">

        @foreach($documents as $doc)

        <li class="list-group-item d-flex align-items-start" data-id="{{ $doc->id }}">
            <span class="me-2 drag-handle" style="cursor: grab;">&#9776;</span>

            <div>
                <strong>{{ $doc->agenda_order }}.</strong>
                {{ $doc->document->title }}
                <br>
                <small>
                    Proponent: {{ $doc->document->member->name }}
                </small>

                <div class="mt-2">
                    <a href="{{ config('app.lis_storage_url').'/'.$doc->document->file_path }}"
                       target="_blank"
                       class="btn btn-sm btn-primary">
                        View Document
                    </a>
                </div>
            </div>
        </li>

        @endforeach

        </ul>

</div>

@endsection

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('agendaLists');
        console.log('Agenda Sortable init:', !!el, typeof Sortable);
        if (!el || typeof Sortable === 'undefined') return;

        Sortable.create(el, {
            animation: 150,
            handle: '.drag-handle',
            forceFallback: true,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function () {
                const order = [];

                document.querySelectorAll('#agendaLists li').forEach(item => {
                    order.push(item.dataset.id);
                });

                fetch("{{ route('secretary.session.agenda.reorder') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({order: order})
                });
            }
        });
    });
</script>