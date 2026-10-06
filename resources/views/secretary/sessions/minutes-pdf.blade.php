
<img src="https://lis.novulutions.com/default/jones_logo.jfif" alt="logo" style="width: 100px; height: 100px;">
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
    
    <hr>
    
    <hr>
 
    <h3>Agenda Items</h3>
    
    @foreach($documents as $doc)
    
    @php
    $minute = $minutes[$doc->document->id] ?? null;
    @endphp
    
    <h4>
    {{ $doc->agenda_order }}. {{ $doc->document->title }}
    </h4>
    
    <p>
    <strong>Proponent:</strong>
    {{ $doc->document->member->name ?? 'Member' }}
    </p>
    
    <p>
    <strong>Discussion:</strong><br>
    {{ $minute->discussion ?? '' }}
    </p>
    
    <p>
    <strong>Motion:</strong><br>
    {{ $minute->motion ?? '' }}
    </p>
    
    <p>
    <strong>Decision:</strong><br>
    {{ $minute->decision ?? '' }}
    </p>
    
    <hr>
    
    @endforeach

    
<p>
    <strong>Adjournment:</strong>
    {{ $session->adjournment_time }}
    </p>