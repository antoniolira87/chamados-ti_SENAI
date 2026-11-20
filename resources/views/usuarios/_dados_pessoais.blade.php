{{-- Campos pessoais com validação. Usado no cadastro/alteração do administrador e no "Cadastre-se". --}}
@php($usuario ??= null)
<div class="col-md-6 mb-3">
    <label for="celular" class="form-label">Celular</label>
    <input id="celular" type="tel" name="celular" class="form-control @error('celular') is-invalid @enderror"
           value="{{ old('celular', $usuario?->celular_formatado) }}" placeholder="(11) 98765-4321"
           inputmode="numeric" maxlength="15" data-validar="celular" required>
    <div class="invalid-feedback">@error('celular'){{ $message }}@enderror</div>
</div>
