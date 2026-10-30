<form method="POST" action="{{ $action }}">
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif

    <div class="campo">
        <label for="paciente_id">Paciente</label>
        <select id="paciente_id" name="paciente_id" required>
            <option value="">Selecione...</option>
            @foreach ($pacientes as $paciente)
                <option value="{{ $paciente->id }}"
                    @selected((int) old('paciente_id', $consulta?->paciente_id) === $paciente->id)>
                    {{ $paciente->nome }}
                </option>
            @endforeach
        </select>
        @error('paciente_id') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="campo">
        <label for="medico_id">Médico</label>
        <select id="medico_id" name="medico_id" required>
            <option value="">Selecione...</option>
            @foreach ($medicos as $medico)
                <option value="{{ $medico->id }}"
                    @selected((int) old('medico_id', $consulta?->medico_id) === $medico->id)>
                    {{ $medico->nome }} — {{ $medico->especialidade->nome }}
                </option>
            @endforeach
        </select>
        @error('medico_id') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="campo">
        <label for="data_hora">Data e hora</label>
        <input type="datetime-local" id="data_hora" name="data_hora" required
               value="{{ old('data_hora', $consulta?->data_hora?->format('Y-m-d\TH:i')) }}">
        @error('data_hora') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="campo">
        <label for="status">Status</label>
        <select id="status" name="status">
            @foreach ($statusList as $status)
                <option value="{{ $status }}"
                    @selected(old('status', $consulta?->status ?? 'Agendada') === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="campo">
        <label for="motivo">Motivo</label>
        <input type="text" id="motivo" name="motivo" maxlength="200"
               value="{{ old('motivo', $consulta?->motivo) }}">
        @error('motivo') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="campo">
        <label for="valor">Valor (R$)</label>
        <input type="number" id="valor" name="valor" step="0.01" min="0"
               value="{{ old('valor', $consulta?->valor) }}">
        @error('valor') <div class="erro-campo">{{ $message }}</div> @enderror
    </div>

    <div class="acoes">
        <button class="btn" type="submit">Salvar</button>
        <a class="btn btn-sec" href="{{ route('consultas.index') }}">Cancelar</a>
    </div>
</form>
