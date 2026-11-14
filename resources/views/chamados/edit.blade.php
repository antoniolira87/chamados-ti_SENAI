@extends('layouts.app')
@section('title', "Editar {$chamado->numero}")
@section('content')
<div class="sn-titulo">
    <a href="{{ route('chamados.show', $chamado) }}" class="btn btn-sm btn-light" title="Voltar"><i class="bi bi-chevron-left"></i></a>
    <div>
        <div class="sn-tipo">Incidente</div>
        <h1>{{ $chamado->numero }}</h1>
    </div>
</div>
<div class="card"><div class="card-body p-3 p-md-4">
    <form action="{{ route('chamados.update', $chamado) }}" method="POST">
        @method('PUT')
        @include('chamados._form', ['botao' => 'Salvar'])
    </form>
</div></div>
@endsection
