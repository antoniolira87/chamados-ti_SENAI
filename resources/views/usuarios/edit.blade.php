@extends('layouts.app')
@section('title', 'Editar usuário')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <h1 class="h2 mb-4">Editar usuário</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
            @method('PUT')
            @include('usuarios._form', ['botao' => 'Salvar alterações'])
        </form>
    </div></div>
</div></div>
@endsection
