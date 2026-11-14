@extends('layouts.app')
@section('title', 'Criar conta')
@section('content')
<div class="row justify-content-center mt-4"><div class="col-md-10 col-lg-7">
    <h1 class="h3 mb-4 text-center text-white fw-bold">Criar conta</h1>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('register') }}" method="POST" data-validar-form novalidate>
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nome completo</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" minlength="3" maxlength="100" data-validar="nome" required>
                    <div class="invalid-feedback">@error('name'){{ $message }}@enderror</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" maxlength="100" data-validar="email" required>
                    <div class="invalid-feedback">@error('email'){{ $message }}@enderror</div>
                </div>

                @include('usuarios._dados_pessoais')

                <div class="col-12 mb-3">
                    <label for="setor" class="form-label">Setor</label>
                    <input id="setor" name="setor" value="{{ old('setor') }}" class="form-control @error('setor') is-invalid @enderror" maxlength="100" placeholder="Ex.: Financeiro">
                    @error('setor') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" required>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-4">
                    <label for="password_confirmation" class="form-label">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Cadastrar</button>
        </form>
    </div></div>
    <p class="text-center text-white-50 mt-3">Já tem conta? <a class="link-light" href="{{ route('login') }}">Entrar</a></p>
</div></div>

@push('scripts')
    <script src="{{ asset('js/validacao.js') }}"></script>
@endpush
@endsection
