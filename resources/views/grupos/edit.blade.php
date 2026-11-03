@extends('layouts.app')
@section('title', 'Editar grupo')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="sn-titulo"><div><div class="sn-tipo">Administração</div><h1>Editar grupo</h1></div></div>
    <div class="card"><div class="card-body p-4">
        <form action="{{ route('grupos.update', $grupo) }}" method="POST">
            @method('PUT')
            @include('grupos._form', ['botao' => 'Salvar alterações'])
        </form>
    </div></div>
</div></div>
@endsection
