<img src="{{ public_path('default/logo_jones.png') }}" alt="Logo" style="width: 100px; height: 100px;">
<h2 style="text-align:center">
 {{ env('APP_CLIENT_NAME')}}
<br>
{{ env('APP_CLIENT_POSITION')}}
</h2>

<h3 style="text-align:center">
{{ $session->title }}
</h3>

<p style="text-align:center">
{{ \Carbon\Carbon::parse($session->session_date)->format('F d, Y') }}
</p>

<hr>

<h3>Minutes of the Meeting</h3>

<p>
Call to Order: _______________________________
</p>

<p>
Prayer: ____________________________________
</p>

<p>
Roll Call: __________________________________
</p>

<hr>

<h3>Agenda Items</h3>

@foreach($documents as $doc)

<p>

<strong>{{ $doc->agenda_order }}. {{ $doc->document->title }}</strong>

<br>

Proponent: {{ $doc->document->member->name ?? 'Member' }}

<br><br>

Discussion:
____________________________________________________

<br><br>

Motion:
____________________________________________________

<br><br>

Decision:
____________________________________________________

</p>

<hr>

@endforeach

<p>

Adjournment:
_____________________________________________

</p>