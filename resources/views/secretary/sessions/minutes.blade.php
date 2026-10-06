@extends('layouts.app')

@section('content')

<div class="container-fluid py-3">

<!-- Header -->

<div class="d-flex justify-content-between align-items-center mb-3">

<div>
<h4 class="mb-0">{{ $session->title }}</h4>
<small class="text-muted">Minutes Encoding</small>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<span class="badge bg-primary">
{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
</span>

</div>

<form method="POST"
action="{{ route('secretary.minutes.store',$session->id) }}">

@csrf

<div class="card mb-4">

    <div class="card-header">
    <strong>Session Opening</strong>
    </div>
    
    <div class="card-body">
    
    <div class="mb-3">
    
    <label class="form-label fw-semibold">
    Call to Order
    </label>
    
    <input
    type="text"
    name="call_to_order"
    class="form-control"
    value="{{ $session->call_to_order ?? '' }}"
    placeholder="e.g. Hon. Raspado called the meeting to order at 9:00 AM">
    
    </div>
    
    
    <div class="mb-3">
    
    <label class="form-label fw-semibold">
    Prayer
    </label>
    
    <input
    type="text"
    name="prayer"
    class="form-control"
    value="{{ $session->prayer ?? '' }}"
    placeholder="e.g. Led by Hon. Reyes">
    
    </div>
    
    
    <div>
    
    <label class="form-label fw-semibold">
    Roll Call
    </label>
    
    <textarea
    name="roll_call"
    class="form-control"
    rows="2"
    placeholder="Members present">{{ $session->roll_call ?? '' }}</textarea>
    
    </div>

    <div class="mb-3">

        <label class="form-label fw-semibold">
        Adjournment Time
        </label>
        
        <input
        type="text"
        name="adjournment_time"
        class="form-control"
        value="{{ $session->adjournment_time ?? '' }}"
        placeholder="e.g. 11:45 AM">
        
        </div>
    
    </div>
    
    </div>

<div class="row">

<!-- LEFT PANEL : AGENDA LIST -->

<div class="col-md-4">

<div class="card shadow-sm sticky-top" style="top:50px;">

<div class="card-header bg-light">
<strong>Agenda Items</strong>
</div>

<div class="list-group list-group-flush">

@foreach($documents as $doc)

<a href="#agenda{{ $doc->id }}"
    class="list-group-item list-group-item-action agenda-link"
    data-index="{{ $loop->index }}">
    

<strong>{{ $doc->agenda_order }}.</strong>
{{ $doc->document->title }}

<div class="small text-muted">
Proponent: {{ $doc->document->member->name ?? 'Member' }}
</div>

</a>

@endforeach

</div>

</div>

</div>

<!-- RIGHT PANEL : MINUTES EDITOR -->

<div class="col-md-8">

@foreach($documents as $doc)

<div id="agenda{{ $doc->id }}"
    class="card shadow-sm mb-4 agenda-card"
    data-index="{{ $loop->index }}">

<div class="card-header bg-white">

<strong>
Agenda {{ $doc->agenda_order }}
</strong>

<div class="small text-muted">
{{ $doc->document->title }}
</div>

</div>

<div class="card-body">

    

<!-- Discussion -->

<div class="mb-3">

<label class="form-label fw-semibold">
Discussion
</label>

<textarea
name="minutes[{{ $doc->document->id }}][discussion]"
class="form-control"
rows="4"
placeholder="Enter discussion notes...">{{ $minutes[$doc->document->id]->discussion ?? '' }}</textarea>

</div>

<!-- Motion -->

<div class="mb-3">

<label class="form-label fw-semibold">
Motion
</label>

<textarea
name="minutes[{{ $doc->document->id }}][motion]"
class="form-control"
rows="2"
placeholder="Enter motion details...">{{ $minutes[$doc->document->id]->motion ?? '' }}</textarea>

</div>

<!-- Decision -->

<div>

<label class="form-label fw-semibold">
Decision
</label>

<textarea
name="minutes[{{ $doc->document->id }}][decision]"
class="form-control"
rows="2"
placeholder="Enter decision or resolution...">{{ $minutes[$doc->document->id]->decision ?? '' }}</textarea>

</div>

</div>

</div>

@endforeach

</div>

</div>

<!-- Save Button -->

<div class="text-end mt-3">

<button class="btn btn-primary px-4 shadow-sm">
Save Minutes
</button>

@if($minutes->count() > 0)
<a href="{{ route('secretary.minutes.review',$session->id) }}"
    class="btn btn-success">
        
        Review Minutes
        
        </a>
    @endif
</div>

</form>

</div>

@endsection

<style>
.agenda-link.active{
background:#0d6efd;
color:white;
font-weight:600;
}
</style>

@push('scripts')

<script>

const agendaCards = document.querySelectorAll('.agenda-card');
const agendaLinks = document.querySelectorAll('.agenda-link');

function setActive(index)
{
    agendaLinks.forEach(link => link.classList.remove('active'));

    if(agendaLinks[index])
        agendaLinks[index].classList.add('active');
}

window.addEventListener('scroll', function(){

    let current = 0;

    agendaCards.forEach((card, index) => {

        const rect = card.getBoundingClientRect();

        if(rect.top <= 150)
        {
            current = index;
        }

    });
    console.log(current);

    setActive(current);

});

</script>

@endpush
