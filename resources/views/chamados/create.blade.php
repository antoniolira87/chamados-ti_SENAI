@extends('layouts.app')
@section('title', 'Abrir chamado')
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <h1 class="h2 mb-4">Abrir chamado</h1>
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <form action="{{ route('chamados.store') }}" method="POST">
            @include('chamados._form', ['botao' => 'Abrir chamado'])
        </form>
    </div></div>
</div></div>
@endsection
