@extends('layouts.app')
@section('title', 'Editar categoria')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="sn-titulo"><div><div class="sn-tipo">Administração</div><h1>Editar categoria</h1></div></div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
            @method('PUT')
            @include('categorias._form', ['botao' => 'Salvar alterações'])
        </form>
    </div></div>
</div></div>
@endsection
