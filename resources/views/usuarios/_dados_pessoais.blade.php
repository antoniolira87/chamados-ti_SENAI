{{-- Campos pessoais com validação. Usado no cadastro/alteração do administrador e no "Cadastre-se". --}}
@php
    $usuario ??= null;
    $hoje = now();
    $nascimentoMax = $hoje->copy()->subYears(\App\Http\Requests\UsuarioRequest::IDADE_MINIMA)->format('Y-m-d');
    $nascimentoMin = $hoje->copy()->subYears(\App\Http\Requests\UsuarioRequest::IDADE_MAXIMA + 1)->addDay()->format('Y-m-d');
@endphp
<div class="col-md-6 mb-3">
    <label for="data_nascimento" class="form-label">Data de nascimento</label>
    <input id="data_nascimento" type="date" name="data_nascimento"
           class="form-control @error('data_nascimento') is-invalid @enderror"
           value="{{ old('data_nascimento', $usuario?->data_nascimento?->format('Y-m-d')) }}"
           min="{{ $nascimentoMin }}" max="{{ $nascimentoMax }}" data-validar="nascimento" required>
    <div class="invalid-feedback">@error('data_nascimento'){{ $message }}@enderror</div>
    <div class="form-text" data-idade>Mínimo de 18 anos completos.</div>
</div>
<div class="col-md-6 mb-3">
    <label for="cpf" class="form-label">CPF</label>
    <input id="cpf" name="cpf" class="form-control @error('cpf') is-invalid @enderror"
           value="{{ old('cpf', $usuario?->cpf_formatado) }}" placeholder="000.000.000-00"
           inputmode="numeric" maxlength="14" autocomplete="off" data-validar="cpf" required>
    <div class="invalid-feedback">@error('cpf'){{ $message }}@enderror</div>
</div>
<div class="col-md-6 mb-3">
    <label for="celular" class="form-label">Celular</label>
    <input id="celular" type="tel" name="celular" class="form-control @error('celular') is-invalid @enderror"
           value="{{ old('celular', $usuario?->celular_formatado) }}" placeholder="(11) 98765-4321"
           inputmode="numeric" maxlength="15" data-validar="celular" required>
    <div class="invalid-feedback">@error('celular'){{ $message }}@enderror</div>
</div>
<div class="col-md-6 mb-3">
    <label for="cep" class="form-label">CEP</label>
    <input id="cep" name="cep" class="form-control @error('cep') is-invalid @enderror"
           value="{{ old('cep', $usuario?->cep_formatado) }}" placeholder="12345-678"
           inputmode="numeric" maxlength="9" data-validar="cep" required>
    <div class="invalid-feedback">@error('cep'){{ $message }}@enderror</div>
</div>
