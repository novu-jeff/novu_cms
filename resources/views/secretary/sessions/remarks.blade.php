@extends('layouts.app')

@section('content')

<div class="container py-4">
<h1>View Remarks</h1>

<h4>{{ $session->title }}</h4>

<p class="text-muted">
{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
</p>

<hr>

@foreach($documents as $doc)

<div class="card mb-4">

<div class="card-header">

<strong>
{{ $doc->agenda_order }}.
{{ $doc->document->title }}
</strong>

</div>

<div class="card-body">

<p class="text-muted">
Proponent:
{{ $doc->document->memberss->name ?? 'Member' }}
</p>

<h6>Member Remarks</h6>

@php
   // $remarks = ($doc->document->remarks ?? collect())->whereNull('parent_id');

   // dd($doc->document->documentRemarks);
@endphp

@forelse(($doc->document->documentRemarks ?? []) as $remark)
<div class="border rounded p-2 mb-2">

<strong>
{{ $remark->member->member->name ?? 'Member' }}
</strong>

<div class="small text-muted">
{{ $remark->created_at?->diffForHumans() }}
</div>

<p>
{{ $remark->remark ?? '' }}
</p>

<!-- Replies -->

@foreach($remark->replies ?? [] as $reply)

<div class="ms-4 border-start ps-3 mt-2">

<strong>
{{ $reply->member->member->name ?? 'Member' }}
</strong>

<div class="small text-muted">
{{ $reply->created_at?->diffForHumans() }}
</div>

<p>
{{ $reply->remark ?? '' }}
</p>

</div>

@endforeach

</div>

@empty

<p class="text-muted">
No remarks yet.
</p>

@endforelse

</div>

</div>

@endforeach

</div>

@endsection
