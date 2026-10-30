@extends('layouts.app')

@section('titulo', 'Editar consulta')

@section('conteudo')
<div class="cartao">
    <h1>Editar consulta #{{ $consulta->id }}</h1>
    @include('consultas._form', [
        'action' => route('consultas.update', $consulta),
        'metodo' => 'PUT',
    ])
</div>
@endsection
