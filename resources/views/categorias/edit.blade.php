@extends('layouts.app')
@section('title', 'Editar categoria')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <h1 class="h2 mb-4">Editar categoria</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('categorias.update', $categoria) }}" method="POST">
            @method('PUT')
            @include('categorias._form', ['botao' => 'Salvar alterações'])
        </form>
    </div></div>
</div></div>
@endsection
