@extends('layouts.app')
@section('title', 'Novo usuário')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="sn-titulo"><div><div class="sn-tipo">Administração</div><h1>Novo usuário</h1></div></div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @include('usuarios._form', ['botao' => 'Cadastrar usuário'])
        </form>
    </div></div>
</div></div>
@endsection
