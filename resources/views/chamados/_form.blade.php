@csrf
<div class="mb-3">
    <label for="titulo" class="form-label">Título</label>
    <input id="titulo" name="titulo" class="form-control @error('titulo') is-invalid @enderror"
           value="{{ old('titulo', $chamado->titulo ?? '') }}" maxlength="255" required placeholder="Resumo do problema">
    @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="categoria_id" class="form-label">Categoria</label>
        <select id="categoria_id" name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror" required>
            <option value="">Selecione...</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_id', $chamado->categoria_id ?? '') == $categoria->id)>{{ $categoria->nome }}</option>
            @endforeach
        </select>
        @error('categoria_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="prioridade" class="form-label">Prioridade</label>
        <select id="prioridade" name="prioridade" class="form-select @error('prioridade') is-invalid @enderror" required>
            @foreach (\App\Models\Chamado::PRIORIDADES as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('prioridade', $chamado->prioridade ?? 'media') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        @error('prioridade') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="mb-4">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea id="descricao" name="descricao" rows="6" class="form-control @error('descricao') is-invalid @enderror"
              maxlength="5000" required placeholder="Descreva o problema, quando começou, mensagens de erro...">{{ old('descricao', $chamado->descricao ?? '') }}</textarea>
    @error('descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $botao }}</button>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
