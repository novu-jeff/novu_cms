<h2>{{ $session->title }}</h2>

<p>Date:
{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
</p>

<hr>

<h3>Agenda</h3>

@foreach($documents as $doc)

<p>

<strong>{{ $doc->agenda_order }}.</strong>

{{ $doc->document->title }}

<br>

Proponent:
{{ $doc->document->member->name ?? 'Member' }}

</p>

@endforeach