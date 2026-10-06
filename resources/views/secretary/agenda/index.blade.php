@extends('layouts.app')

@section('content')

<div class="container">

<h4>Session Agenda</h4>

<table class="table table-bordered">

<thead>
<tr>
<th width="80">Order</th>
<th>Title</th>
<th width="150">Move</th>
</tr>
</thead>

<tbody id="agendaList">

@foreach($documents as $doc)

<tr data-id="{{ $doc->id }}">

<td>{{ $doc->agenda_order }}</td>

<td>{{ $doc->title }}</td>

<td>

<button class="btn btn-sm btn-secondary move-up">
↑
</button>

<button class="btn btn-sm btn-secondary move-down">
↓
</button>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@endsection


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>

let el = document.getElementById('agendaList');

Sortable.create(el, {

    animation:150,

    onEnd:function(){

        let order=[];

        document.querySelectorAll('#agendaList tr').forEach(row=>{
            order.push(row.dataset.id);
        });

        fetch("{{ route('secretary.agenda.reorder') }}",{

            method:"POST",

            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },

            body:JSON.stringify({order:order})

        });

    }

});

</script>