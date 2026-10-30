@extends('layouts.app')

@section('titulo', 'Consultas')

@section('conteudo')
<div class="cartao">
    <div class="topo">
        <h1>Consultas</h1>
        <a class="btn" href="{{ route('consultas.create') }}">+ Nova consulta</a>
    </div>

    <form method="GET" action="{{ route('consultas.index') }}" class="filtros">
        <div class="campo">
            <label for="busca">Paciente</label>
            <input type="text" id="busca" name="busca" value="{{ request('busca') }}" placeholder="Nome do paciente">
        </div>
        <div class="campo">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Todos</option>
                @foreach ($statusList as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn btn-sec" href="{{ route('consultas.index') }}">Limpar</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Paciente</th>
                <th>Médico</th>
                <th>Status</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($consultas as $consulta)
                <tr>
                    <td>{{ $consulta->data_hora->format('d/m/Y H:i') }}</td>
                    <td>{{ $consulta->paciente->nome }}</td>
                    <td>
                        {{ $consulta->medico->nome }}<br>
                        <small>{{ $consulta->medico->especialidade->nome }}</small>
                    </td>
                    <td><span class="selo {{ strtolower($consulta->status) }}">{{ $consulta->status }}</span></td>
                    <td>{{ $consulta->valor !== null ? 'R$ ' . number_format($consulta->valor, 2, ',', '.') : '—' }}</td>
                    <td>
                        <div class="acoes">
                            <a class="btn btn-sec" href="{{ route('consultas.show', $consulta) }}">Ver</a>
                            <a class="btn btn-sec" href="{{ route('consultas.edit', $consulta) }}">Editar</a>
                            <form method="POST" action="{{ route('consultas.destroy', $consulta) }}"
                                  onsubmit="return confirm('Excluir esta consulta?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-perigo" type="submit">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhuma consulta encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="paginacao">
        @if ($consultas->onFirstPage())
            <span class="desativado">« Anterior</span>
        @else
            <a href="{{ $consultas->previousPageUrl() }}">« Anterior</a>
        @endif

        <span>Página {{ $consultas->currentPage() }} de {{ max($consultas->lastPage(), 1) }}</span>

        @if ($consultas->hasMorePages())
            <a href="{{ $consultas->nextPageUrl() }}">Próxima »</a>
        @else
            <span class="desativado">Próxima »</span>
        @endif
    </div>
</div>
@endsection
