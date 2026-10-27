@extends('layouts.app')
@section('title', 'Novo incidente')
@section('content')
<div class="sn-titulo">
    <a href="{{ route('chamados.index') }}" class="btn btn-sm btn-light" title="Voltar"><i class="bi bi-chevron-left"></i></a>
    <div>
        <div class="sn-tipo">Incidente</div>
        <h1>Novo registro</h1>
    </div>
</div>
<div class="card"><div class="card-body p-3 p-md-4">
    <form action="{{ route('chamados.store') }}" method="POST">
        @include('chamados._form', ['botao' => 'Enviar'])
    </form>
</div></div>
@endsection
