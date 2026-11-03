@csrf
<div class="mb-3">
    <label for="nome" class="form-label">Nome</label>
    <input id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror"
           value="{{ old('nome', $grupo->nome ?? '') }}" maxlength="100" required>
    @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="mb-4">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea id="descricao" name="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror">{{ old('descricao', $grupo->descricao ?? '') }}</textarea>
    @error('descricao') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $botao }}</button>
    <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
