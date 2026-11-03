@extends('layouts.app')

@section('titulo', 'Consultas | Sistema Hospitalar')

@section('conteudo')
<section class="cabecalho-pagina">
    <div>
        <p class="sobrelinha">ATENDIMENTO / AGENDA</p>
        <h1>Consultas</h1>
        <p class="texto-suave">Acompanhe os atendimentos e organize a agenda médica.</p>
    </div>
    <a class="btn" href="{{ route('consultas.create') }}">+ Nova consulta</a>
</section>

<section class="indicadores" aria-label="Resumo de todas as consultas">
    <a class="indicador" href="{{ route('consultas.index') }}">
        <span>Total de consultas</span><strong>{{ number_format($resumo['total'], 0, ',', '.') }}</strong><small>Todos os atendimentos</small>
    </a>
    <a class="indicador indicador-azul" href="{{ route('consultas.index', ['status' => 'Agendada']) }}">
        <span>Agendadas</span><strong>{{ number_format($resumo['agendadas'], 0, ',', '.') }}</strong><small>Consultas com agendamento</small>
    </a>
    <a class="indicador indicador-verde" href="{{ route('consultas.index', ['status' => 'Realizada']) }}">
        <span>Realizadas</span><strong>{{ number_format($resumo['realizadas'], 0, ',', '.') }}</strong><small>Atendimentos concluídos</small>
    </a>
    <a class="indicador indicador-cinza" href="{{ route('consultas.index', ['status' => 'Cancelada']) }}">
        <span>Canceladas</span><strong>{{ number_format($resumo['canceladas'], 0, ',', '.') }}</strong><small>Agendamentos cancelados</small>
    </a>
</section>

<div class="cartao painel-consultas">
    <div class="titulo-painel"><h2>Agenda de atendimentos</h2><span class="texto-suave">Mais recentes primeiro</span></div>
    @if ($errors->any())
        <div class="alerta erro" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="GET" action="{{ route('consultas.index') }}" class="filtros filtros-agenda">
        <div class="campo campo-busca">
            <label for="busca">Buscar atendimento</label>
            <input type="search" id="busca" name="busca" maxlength="150" value="{{ old('busca', $filtros['busca'] ?? '') }}" placeholder="Paciente, CPF ou médico">
        </div>
        <div class="campo">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Todos os status</option>
                @foreach ($statusList as $status)
                    <option value="{{ $status }}" @selected(old('status', $filtros['status'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo">
            <label for="medico_id">Médico</label>
            <select id="medico_id" name="medico_id">
                <option value="">Todos os médicos</option>
                @foreach ($medicos as $medico)
                    <option value="{{ $medico->id }}" @selected((string) old('medico_id', $filtros['medico_id'] ?? '') === (string) $medico->id)>{{ $medico->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="campo"><label for="data_inicio">De</label><input type="date" id="data_inicio" name="data_inicio" value="{{ old('data_inicio', $filtros['data_inicio'] ?? '') }}"></div>
        <div class="campo"><label for="data_fim">Até</label><input type="date" id="data_fim" name="data_fim" value="{{ old('data_fim', $filtros['data_fim'] ?? '') }}"></div>
        <div class="campo">
            <label for="por_pagina">Por página</label>
            <select id="por_pagina" name="por_pagina">@foreach ([10, 25, 50] as $quantidade)<option value="{{ $quantidade }}" @selected((int) old('por_pagina', $filtros['por_pagina'] ?? 10) === $quantidade)>{{ $quantidade }} registros</option>@endforeach</select>
        </div>
        <div class="acoes filtros-acoes"><button class="btn" type="submit">Aplicar filtros</button><a class="btn btn-sec" href="{{ route('consultas.index') }}">Limpar</a></div>
    </form>

    <div class="resultado-resumo" aria-live="polite">
        <strong>{{ number_format($consultas->total(), 0, ',', '.') }} {{ $consultas->total() === 1 ? 'consulta encontrada' : 'consultas encontradas' }}</strong>
        @if ($consultas->total())<span class="texto-suave">Exibindo {{ $consultas->firstItem() ?? 0 }}–{{ $consultas->lastItem() ?? 0 }}</span>@endif
    </div>
    <div class="tabela-container" role="region" aria-label="Lista de consultas" tabindex="0">
        <table>
            <thead><tr><th scope="col">Data e hora</th><th scope="col">Paciente</th><th scope="col">Médico / especialidade</th><th scope="col">Status</th><th scope="col">Registros clínicos</th><th scope="col">Valor</th><th scope="col">Ações</th></tr></thead>
            <tbody>
            @forelse ($consultas as $consulta)
                <tr>
                    <td class="sem-quebra"><strong>{{ $consulta->data_hora->format('d/m/Y') }}</strong><small class="linha-secundaria">{{ $consulta->data_hora->format('H:i') }} · #{{ $consulta->id }}</small></td>
                    <td><strong>{{ $consulta->paciente->nome }}</strong><small class="linha-secundaria">{{ $consulta->paciente->convenio?->nome ?? 'Sem convênio' }}</small></td>
                    <td>{{ $consulta->medico->nome }}<small class="linha-secundaria">{{ $consulta->medico->especialidade->nome }}</small></td>
                    <td><span class="selo {{ strtolower($consulta->status) }}">{{ $consulta->status }}</span></td>
                    <td><small>{{ $consulta->prontuarios_count }} prontuário(s)</small><small class="linha-secundaria">{{ $consulta->prescricoes_count }} prescrição(ões)</small></td>
                    <td class="sem-quebra">{{ $consulta->valor !== null ? 'R$ ' . number_format((float) $consulta->valor, 2, ',', '.') : '—' }}</td>
                    <td><div class="acoes acoes-linha">
                        <a class="link-acao" href="{{ route('consultas.show', $consulta) }}" aria-label="Ver consulta {{ $consulta->id }}">Ver</a>
                        <a class="link-acao" href="{{ route('consultas.edit', $consulta) }}" aria-label="Editar consulta {{ $consulta->id }}">Editar</a>
                        @if ($consulta->prontuarios_count === 0 && $consulta->prescricoes_count === 0)
                            <form method="POST" action="{{ route('consultas.destroy', $consulta) }}" onsubmit="return confirm('Excluir esta consulta?')">@csrf @method('DELETE')<button class="link-acao link-perigo" type="submit" aria-label="Excluir consulta {{ $consulta->id }}">Excluir</button></form>
                        @endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="estado-vazio"><strong>Nenhuma consulta encontrada.</strong><p class="texto-suave">Altere os filtros ou cadastre uma nova consulta para começar.</p><a class="btn btn-sec" href="{{ route('consultas.create') }}">Nova consulta</a></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <nav class="paginacao" aria-label="Paginação de consultas">
        @if ($consultas->onFirstPage())<span class="btn btn-sec desativado" aria-disabled="true">← Anterior</span>@else<a class="btn btn-sec" href="{{ $consultas->previousPageUrl() }}" rel="prev">← Anterior</a>@endif
        <span>Página {{ $consultas->currentPage() }} de {{ max($consultas->lastPage(), 1) }}</span>
        @if ($consultas->hasMorePages())<a class="btn btn-sec" href="{{ $consultas->nextPageUrl() }}" rel="next">Próxima →</a>@else<span class="btn btn-sec desativado" aria-disabled="true">Próxima →</span>@endif
    </nav>
</div>
@endsection
