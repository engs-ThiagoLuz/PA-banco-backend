@extends('layouts.app')

@section('titulo', 'Nova consulta')

@section('conteudo')
<div class="cartao">
    <h1>Nova consulta</h1>
    @include('consultas._form', [
        'consulta' => null,
        'action'   => route('consultas.store'),
        'metodo'   => 'POST',
    ])
</div>
@endsection
