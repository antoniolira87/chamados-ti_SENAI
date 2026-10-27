@extends('layouts.app')
@section('title', 'Nova categoria')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <h1 class="h2 mb-4">Nova categoria</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('categorias.store') }}" method="POST">
            @include('categorias._form', ['botao' => 'Cadastrar categoria'])
        </form>
    </div></div>
</div></div>
@endsection
