@csrf
@php
    $impacto = (int) old('impacto', $chamado->impacto ?? 3);
    $urgencia = (int) old('urgencia', $chamado->urgencia ?? 3);
    $prioridade = \App\Models\Chamado::calcularPrioridade($impacto, $urgencia);
@endphp
<div class="row">
    <div class="col-lg-6">
        <div class="sn-campo">
            <span class="rotulo">Número</span>
            <input class="form-control form-control-sm" value="{{ $chamado->numero ?? '(gerado ao salvar)' }}" readonly aria-label="Número">
        </div>
        <div class="sn-campo">
            <span class="rotulo">Solicitante</span>
            <input class="form-control form-control-sm" value="{{ isset($chamado) ? $chamado->solicitante->name : auth()->user()->name }}" readonly aria-label="Solicitante">
        </div>
        <div class="sn-campo">
            <label for="categoria_id" class="obrigatorio">Categoria</label>
            <div>
                <select id="categoria_id" name="categoria_id" class="form-select form-select-sm @error('categoria_id') is-invalid @enderror" required>
                    <option value="">-- Nenhum --</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(old('categoria_id', $chamado->categoria_id ?? '') == $categoria->id)>{{ $categoria->nome }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="sn-campo">
            <label for="impacto" class="obrigatorio">Impacto</label>
            <div>
                <select id="impacto" name="impacto" class="form-select form-select-sm @error('impacto') is-invalid @enderror" required>
                    @foreach (\App\Models\Chamado::IMPACTOS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected($impacto === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
                @error('impacto') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="sn-campo">
            <label for="urgencia" class="obrigatorio">Urgência</label>
            <div>
                <select id="urgencia" name="urgencia" class="form-select form-select-sm @error('urgencia') is-invalid @enderror" required>
                    @foreach (\App\Models\Chamado::URGENCIAS as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected($urgencia === $valor)>{{ $rotulo }}</option>
                    @endforeach
                </select>
                @error('urgencia') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="sn-campo">
            <span class="rotulo">Prioridade</span>
            <div class="d-flex align-items-center gap-2">
                <input id="prioridade" class="form-control form-control-sm" value="{{ \App\Models\Chamado::PRIORIDADES[$prioridade] }}" readonly aria-label="Prioridade calculada">
                <a href="#matriz" data-bs-toggle="collapse" class="small text-nowrap" title="Como a prioridade é calculada"><i class="bi bi-info-circle"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="collapse" id="matriz">
    <div class="card card-body bg-light small mb-3">
        <p class="mb-2">A prioridade é calculada automaticamente a partir do <strong>Impacto</strong> (quantas pessoas/serviços são afetados) e da <strong>Urgência</strong> (quão rápido precisa ser resolvido):</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered text-center mb-0 bg-white">
                <thead><tr><th class="text-start">Impacto \ Urgência</th>@foreach (\App\Models\Chamado::URGENCIAS as $rotulo)<th>{{ $rotulo }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach (\App\Models\Chamado::IMPACTOS as $i => $rotuloImpacto)
                    <tr>
                        <th class="text-start">{{ $rotuloImpacto }}</th>
                        @foreach (\App\Models\Chamado::URGENCIAS as $u => $rotuloUrgencia)
                            @php $p = \App\Models\Chamado::calcularPrioridade($i, $u); @endphp
                            <td><span class="sn-ponto sn-prio-{{ $p }}"></span>{{ \App\Models\Chamado::PRIORIDADES[$p] }}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="sn-campo sn-campo-largo">
    <label for="titulo" class="obrigatorio">Descrição resumida</label>
    <div>
        <input id="titulo" name="titulo" class="form-control form-control-sm @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $chamado->titulo ?? '') }}" maxlength="255" required placeholder="Resumo do problema em uma linha">
        @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="sn-campo sn-campo-largo align-items-start">
    <label for="descricao" class="obrigatorio pt-1">Descrição</label>
    <div>
        <textarea id="descricao" name="descricao" rows="6" class="form-control form-control-sm @error('descricao') is-invalid @enderror"
                  maxlength="5000" required placeholder="O que aconteceu, desde quando, mensagens de erro, quem é afetado...">{{ old('descricao', $chamado->descricao ?? '') }}</textarea>
        @error('descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="sn-campo sn-campo-largo">
    <span></span>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">{{ $botao }}</button>
        <a href="{{ isset($chamado) ? route('chamados.show', $chamado) : route('chamados.index') }}" class="btn btn-light btn-sm">Cancelar</a>
    </div>
</div>

@push('scripts')
<script>
    // Mostra a prioridade calculada pela matriz enquanto o usuário escolhe.
    (function () {
        const rotulos = @json(\App\Models\Chamado::PRIORIDADES);
        const impacto = document.getElementById('impacto');
        const urgencia = document.getElementById('urgencia');
        const atualizar = () => {
            document.getElementById('prioridade').value = rotulos[Number(impacto.value) + Number(urgencia.value) - 1];
        };
        impacto.addEventListener('change', atualizar);
        urgencia.addEventListener('change', atualizar);
    })();
</script>
@endpush
