@extends('layouts.app')

@section('titulo', 'Consulta #' . $consulta->id)

@section('conteudo')
<div class="cartao">
    <div class="topo">
        <h1>Consulta #{{ $consulta->id }}</h1>
        <div class="acoes">
            <a class="btn btn-sec" href="{{ route('consultas.edit', $consulta) }}">Editar</a>
            <a class="btn btn-sec" href="{{ route('consultas.index') }}">Voltar</a>
        </div>
    </div>

    <dl>
        <dt>Data/Hora</dt>
        <dd>{{ $consulta->data_hora->format('d/m/Y H:i') }}</dd>

        <dt>Status</dt>
        <dd><span class="selo {{ strtolower($consulta->status) }}">{{ $consulta->status }}</span></dd>

        <dt>Paciente</dt>
        <dd>
            {{ $consulta->paciente->nome }}
            @if ($consulta->paciente->convenio)
                <small>({{ $consulta->paciente->convenio->nome }})</small>
            @endif
        </dd>

        <dt>Médico</dt>
        <dd>{{ $consulta->medico->nome }} — {{ $consulta->medico->especialidade->nome }} (CRM {{ $consulta->medico->crm }})</dd>

        <dt>Motivo</dt>
        <dd>{{ $consulta->motivo ?? '—' }}</dd>

        <dt>Valor</dt>
        <dd>{{ $consulta->valor !== null ? 'R$ ' . number_format($consulta->valor, 2, ',', '.') : '—' }}</dd>
    </dl>
</div>

<div class="cartao">
    <h2>Prontuários</h2>
    @forelse ($consulta->prontuarios as $prontuario)
        <p>
            <strong>{{ $prontuario->data_registro->format('d/m/Y H:i') }}</strong>
            — {{ $prontuario->diagnostico ?? 'Sem diagnóstico' }}<br>
            {{ $prontuario->descricao }}
        </p>
    @empty
        <p>Nenhum prontuário registrado para esta consulta.</p>
    @endforelse
</div>

<div class="cartao">
    <h2>Prescrições</h2>
    @forelse ($consulta->prescricoes as $prescricao)
        <p><strong>Emitida em {{ $prescricao->data_emissao->format('d/m/Y') }}</strong></p>
        <ul>
            @foreach ($prescricao->itens as $item)
                <li>
                    {{ $item->medicamento->nome }} — {{ $item->dosagem }}, {{ $item->frequencia }}
                    @if ($item->duracao_dias) por {{ $item->duracao_dias }} dias @endif
                </li>
            @endforeach
        </ul>
    @empty
        <p>Nenhuma prescrição emitida para esta consulta.</p>
    @endforelse
</div>
@endsection
