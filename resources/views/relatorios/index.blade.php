@extends('layouts.app')

@section('titulo', 'Relatórios hospitalares')

@section('conteudo')
<h1>Relatórios hospitalares</h1>
<div class="cartao">
    <form method="GET" action="{{ route('relatorios.index') }}" class="filtros">
        <div class="campo">
            <label for="data_inicio">Data inicial do faturamento</label>
            <input type="date" id="data_inicio" name="data_inicio" value="{{ $filtros['data_inicio'] ?? '' }}">
        </div>
        <div class="campo">
            <label for="data_fim">Data final do faturamento</label>
            <input type="date" id="data_fim" name="data_fim" value="{{ $filtros['data_fim'] ?? '' }}">
        </div>
        <div class="campo">
            <label for="departamento_id">Departamento dos quartos</label>
            <select id="departamento_id" name="departamento_id">
                <option value="">Todos</option>
                @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento->id }}" @selected(($filtros['departamento_id'] ?? '') == $departamento->id)>{{ $departamento->nome }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn btn-sec" href="{{ route('relatorios.index') }}">Limpar</a>
    </form>
    @if ($errors->any())
        <div class="alerta erro" role="alert"><ul>
            @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
        </ul></div>
    @endif
</div>
<div class="cartao" style="overflow-x: auto">
    <h2>Faturamento por médico</h2>
    <p>Apenas consultas realizadas. Sem datas informadas, o período vai do início do mês até hoje.</p>
    <table>
        <thead><tr><th>Médico</th><th>Especialidade</th><th>Consultas realizadas</th><th>Faturamento</th></tr></thead>
        <tbody>
        @forelse ($faturamento as $linha)
            <tr><td>{{ $linha->medico_nome }}</td><td>{{ $linha->especialidade }}</td><td>{{ $linha->total_consultas }}</td><td>R$ {{ number_format($linha->faturamento, 2, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="4">Nenhum médico cadastrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="cartao" style="overflow-x: auto">
    <h2>Ocupação atual dos quartos</h2>
    <p>Considera internações sem data de saída.</p>
    <table>
        <thead><tr><th>Quarto</th><th>Departamento</th><th>Tipo</th><th>Capacidade</th><th>Ocupados</th><th>Vagas</th></tr></thead>
        <tbody>
        @forelse ($ocupacao as $linha)
            <tr><td>{{ $linha->numero }}</td><td>{{ $linha->departamento }}</td><td>{{ $linha->tipo }}</td><td>{{ $linha->capacidade }}</td><td>{{ $linha->ocupados }}</td><td>{{ $linha->vagas }}</td></tr>
        @empty
            <tr><td colspan="6">Nenhum quarto encontrado.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
