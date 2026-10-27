@extends('layouts.app')
@section('title', 'Novo usuário')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <h1 class="h2 mb-4">Novo usuário</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @include('usuarios._form', ['botao' => 'Cadastrar usuário'])
        </form>
    </div></div>
</div></div>
@endsection
