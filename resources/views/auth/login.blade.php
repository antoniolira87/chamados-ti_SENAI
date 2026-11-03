@extends('layouts.app')
@section('title', 'Entrar')
@section('content')
<div class="row justify-content-center mt-4 mt-md-5"><div class="col-sm-10 col-md-6 col-lg-4">
    <div class="text-center text-white mb-4">
        <span class="d-inline-grid rounded-3 mb-2" style="place-items:center;width:56px;height:56px;background:var(--sn-accent);color:var(--sn-header)"><i class="bi bi-headset fs-2"></i></span>
        <h1 class="h3 fw-bold mb-1">HelpDesk TI</h1>
        <p class="text-white-50 mb-0">Gestão de incidentes de TI</p>
    </div>
    <div class="card"><div class="card-body p-4">
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
    <p class="text-center text-white-50 mt-3">Não tem conta? <a class="link-light" href="{{ route('register') }}">Cadastre-se</a></p>
</div></div>
@endsection
