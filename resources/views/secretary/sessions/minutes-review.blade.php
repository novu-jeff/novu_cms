@extends('layouts.app')

@section('content')

<div class="container py-4">

<h3>{{ $session->title }}</h3>

<p class="text-muted">
{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
</p>

<hr>

<p>
<strong>Call to Order:</strong>
{{ $session->call_to_order }}
</p>

<p>
<strong>Prayer:</strong>
{{ $session->prayer }}
</p>

<p>
<strong>Roll Call:</strong>
{{ $session->roll_call }}
</p>

<p>
<strong>Adjournment:</strong>
{{ $session->adjournment_time }}
</p>    

<hr>

@foreach($documents as $doc)

@php
$minute = $minutes[$doc->document->id] ?? null;
@endphp

<div class="card mb-4">

<div class="card-header">

<strong>
{{ $doc->agenda_order }}.
{{ $doc->document->title }}
</strong>

</div>

<div class="card-body">

<p>
<strong>Proponent:</strong>
{{ $doc->document->member->name ?? 'Member' }}
</p>

<p>
<strong>Discussion</strong><br>
{{ $minute->discussion ?? '—' }}
</p>

<p>
<strong>Motion</strong><br>
{{ $minute->motion ?? '—' }}
</p>

<p>
<strong>Decision</strong><br>
{{ $minute->decision ?? '—' }}
</p>

</div>

</div>

@endforeach


<div class="d-flex justify-content-between">

<a href="{{ route('secretary.minutes.index',$session->id) }}"
class="btn btn-warning">

Edit Minutes

</a>

<a href="{{ route('secretary.minutes.generate',$session->id) }}"
class="btn btn-primary">

Generate Final Minutes

</a>

</div>

</div>

@endsection