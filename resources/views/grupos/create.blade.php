@extends('layouts.app')
@section('title', 'Novo grupo')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="sn-titulo"><div><div class="sn-tipo">Administração</div><h1>Novo grupo</h1></div></div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('grupos.store') }}" method="POST">
            @include('grupos._form', ['botao' => 'Cadastrar grupo'])
        </form>
    </div></div>
</div></div>
@endsection
