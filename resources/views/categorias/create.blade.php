@extends('layouts.app')
@section('title', 'Nova categoria')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="sn-titulo"><div><div class="sn-tipo">Administração</div><h1>Nova categoria</h1></div></div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('categorias.store') }}" method="POST">
            @include('categorias._form', ['botao' => 'Cadastrar categoria'])
        </form>
    </div></div>
</div></div>
@endsection
