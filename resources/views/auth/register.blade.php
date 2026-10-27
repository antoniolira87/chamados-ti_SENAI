@extends('layouts.app')
@section('title', 'Criar conta')
@section('content')
<div class="row justify-content-center mt-4"><div class="col-md-8 col-lg-5">
    <h1 class="h3 mb-4 text-center">Criar conta</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" maxlength="255" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="setor" class="form-label">Setor</label>
                <input id="setor" name="setor" value="{{ old('setor') }}" class="form-control @error('setor') is-invalid @enderror" maxlength="100" placeholder="Ex.: Financeiro">
                @error('setor') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirmar senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Cadastrar</button>
        </form>
    </div></div>
    <p class="text-center mt-3">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
</div></div>
@endsection
