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

@if ($errors->any())
    <div class="alerta erro" role="alert"><ul>
        @foreach ($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach
    </ul></div>
@endif

@if ($consulta->status === 'Agendada')
<div class="cartao">
    <h2>Realizar consulta</h2>
    <form method="POST" action="{{ route('consultas.realizar', $consulta) }}">
        @csrf
        <div class="campo">
            <label for="descricao">Descrição do atendimento</label>
            <textarea id="descricao" name="descricao" required>{{ old('descricao') }}</textarea>
        </div>
        <div class="campo">
            <label for="diagnostico">Diagnóstico</label>
            <input id="diagnostico" name="diagnostico" maxlength="200" value="{{ old('diagnostico') }}">
        </div>
        <button class="btn" type="submit">Realizar e registrar prontuário</button>
    </form>
</div>
<div class="cartao">
    <h2>Cancelar consulta</h2>
    <form method="POST" action="{{ route('consultas.cancelar', $consulta) }}">
        @csrf
        <div class="campo">
            <label for="motivo_cancelamento">Motivo do cancelamento (opcional)</label>
            <input id="motivo_cancelamento" name="motivo" maxlength="200" value="{{ old('motivo') }}">
        </div>
        <button class="btn btn-perigo" type="submit">Cancelar consulta</button>
    </form>
</div>
@endif

<div class="cartao">
    <h2>Histórico de status</h2>
    <div style="overflow-x: auto">
        <table>
            <thead><tr><th>Data e hora</th><th>Status anterior</th><th>Novo status</th><th>Usuário do banco</th></tr></thead>
            <tbody>
            @forelse ($historico as $alteracao)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($alteracao->alterado_em)->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $alteracao->status_anterior ?? 'Cadastro' }}</td>
                    <td>{{ $alteracao->status_novo }}</td>
                    <td>{{ $alteracao->usuario_banco }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Nenhuma alteração registrada. A carga inicial não gera histórico.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
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
