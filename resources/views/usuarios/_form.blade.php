@csrf
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Nome</label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $usuario->name ?? '') }}" maxlength="255" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $usuario->email ?? '') }}" maxlength="255" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="perfil" class="form-label">Perfil</label>
        <select id="perfil" name="perfil" class="form-select @error('perfil') is-invalid @enderror" required>
            @foreach (\App\Models\User::PERFIS as $valor => $rotulo)
                <option value="{{ $valor }}" @selected(old('perfil', $usuario->perfil ?? 'usuario') === $valor)>{{ $rotulo }}</option>
            @endforeach
        </select>
        @error('perfil') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="setor" class="form-label">Setor</label>
        <input id="setor" name="setor" class="form-control @error('setor') is-invalid @enderror"
               value="{{ old('setor', $usuario->setor ?? '') }}" maxlength="100">
        @error('setor') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="grupo_id" class="form-label">Grupo de atribuição <small class="text-secondary">(técnicos)</small></label>
        <select id="grupo_id" name="grupo_id" class="form-select @error('grupo_id') is-invalid @enderror">
            <option value="">-- Nenhum --</option>
            @foreach ($grupos as $grupo)
                <option value="{{ $grupo->id }}" @selected(old('grupo_id', $usuario->grupo_id ?? '') == $grupo->id)>{{ $grupo->nome }}</option>
            @endforeach
        </select>
        @error('grupo_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3"></div>
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">Senha @isset($usuario)<small class="text-secondary">(deixe em branco para manter)</small>@endisset</label>
        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" @empty($usuario) required @endempty>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-4">
        <label for="password_confirmation" class="form-label">Confirmar senha</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
    </div>
</div>
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $botao }}</button>
    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
</div>
