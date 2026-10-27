@if (session('sucesso'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('sucesso') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif
@if (session('erro'))
    <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('erro') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
@endif
