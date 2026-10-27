@extends('layouts.app')
@section('title', 'Entrar')
@section('content')
<div class="row justify-content-center mt-5"><div class="col-md-6 col-lg-4">
    <div class="text-center mb-4">
        <i class="bi bi-headset display-4 text-primary"></i>
        <h1 class="h3 mt-2">HelpDesk TI</h1>
        <p class="text-secondary">Sistema de chamados de TI</p>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="lembrar" id="lembrar">
                <label class="form-check-label" for="lembrar">Lembrar de mim</label>
            </div>
            <button class="btn btn-primary w-100" type="submit">Entrar</button>
        </form>
    </div></div>
    <p class="text-center mt-3">Não tem conta? <a href="{{ route('register') }}">Cadastre-se</a></p>
</div></div>
@endsection
