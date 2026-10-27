# HelpDesk TI — Sistema de Chamados de TI (Laravel 13)

Projeto da atividade avaliativa **Desenvolvimento app no Laravel**, construído seguindo o roteiro
*Guia prático de Laravel 13* (cadastro de clientes) do Prof. Wagner, aplicado ao tema
**Sistema de chamados de TI**.

## Funcionalidades

- **Cadastro de usuários** com login, logout e auto-cadastro (perfil *Usuário*).
- **Perfis de acesso**: Administrador, Técnico e Usuário (autorização com *Gates*).
- **Abertura de chamados** com título, descrição, **categoria** e **prioridade** (Baixa, Média, Alta, Urgente).
- **Técnico responsável**: atribuição por técnico/admin ou botão *Assumir* no chamado.
- **Mudança de status** (Aberto → Em andamento → Aguardando usuário → Resolvido → Fechado),
  com **histórico** de alterações e comentários em linha do tempo.
- **Listagem com filtros** por busca (título, descrição ou nº), status, prioridade, categoria e técnico, com paginação.
- **Painel (dashboard)** com totais por status, pendências por prioridade, chamados recentes e “meus atendimentos”.
- **CRUD de categorias** e **CRUD de usuários** (somente administrador).
- Usuário comum vê apenas os próprios chamados e só pode editar enquanto o chamado está *Aberto*.
- Validação no servidor com mensagens em português, proteção CSRF, `@method('PUT'/'PATCH'/'DELETE')`.
- Interface responsiva com **Bootstrap 5.3.8** (CDN) e Bootstrap Icons.
- Testes automatizados (PHPUnit) cobrindo as principais regras.

## Conceitos do guia aplicados

| Guia (Clientes)                         | Este projeto                                                         |
|-----------------------------------------|----------------------------------------------------------------------|
| `make:model Cliente -m`                 | Models `Chamado`, `Categoria`, `ChamadoHistorico`, `User` + migrations |
| `make:controller --resource`            | `ChamadoController`, `CategoriaController`, `UserController`         |
| `Route::resource`                       | `chamados`, `categorias`, `usuarios` + rota extra `PATCH chamados/{chamado}/status` |
| `$request->validate([...])`             | Validação nos controllers + Form Request `ChamadoRequest`            |
| Views Blade `_form` reutilizável        | `_form.blade.php` em cada módulo, layout `layouts/app.blade.php`     |
| Bootstrap + `Paginator::useBootstrapFive()` | Mesmo padrão, em `AppServiceProvider`                            |

## Estrutura principal

```
app/Http/Controllers/  AuthController, DashboardController, ChamadoController, CategoriaController, UserController
app/Http/Requests/     ChamadoRequest
app/Models/            User, Chamado, Categoria, ChamadoHistorico
app/Providers/         AppServiceProvider (Gates de autorização + paginação Bootstrap)
database/migrations/   users (perfil, setor), categorias, chamados, chamado_historicos
database/seeders/      DatabaseSeeder (usuários, categorias e chamados de exemplo)
resources/views/       layouts, auth, dashboard, chamados, categorias, usuarios
routes/web.php         rotas web (guest / auth / admin)
tests/Feature/         ChamadosTest
```

## Requisitos

- PHP 8.3+ (extensões `pdo_mysql`, `mbstring`, `xml`, `curl`…)
- Composer
- MySQL/MariaDB (ou SQLite)

## Instalação

```bash
git clone <url-deste-repositorio> chamados-ti
cd chamados-ti
composer install
cp .env.example .env        # no Windows: copy .env.example .env
php artisan key:generate
```

Crie um banco vazio chamado `chamados_ti` no MySQL (phpMyAdmin, Adminer ou cliente MySQL) e ajuste o `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chamados_ti
DB_USERNAME=root
DB_PASSWORD=
```

> Para usar SQLite em vez de MySQL: `DB_CONNECTION=sqlite` e crie o arquivo `database/database.sqlite`.

Crie as tabelas e os dados de exemplo e inicie o servidor:

```bash
php artisan config:clear
php artisan migrate --seed
php artisan serve
```

Acesse <http://127.0.0.1:8000>.

## Usuários de demonstração

Todos com a senha **`senha123`**:

| Perfil        | E-mail                   |
|---------------|--------------------------|
| Administrador | `admin@helpdesk.test`    |
| Técnico       | `carlos@helpdesk.test`   |
| Técnico       | `fernanda@helpdesk.test` |
| Usuário       | `maria@helpdesk.test`    |
| Usuário       | `joao@helpdesk.test`     |

## Permissões

| Ação                                   | Usuário | Técnico | Admin |
|----------------------------------------|:-------:|:-------:|:-----:|
| Abrir chamado                          | ✔       | ✔       | ✔     |
| Ver chamados                           | próprios | todos  | todos |
| Editar chamado                         | próprio, se *Aberto* | ✔ | ✔ |
| Mudar status / atribuir técnico        |         | ✔       | ✔     |
| Excluir chamado                        |         |         | ✔     |
| Gerenciar categorias e usuários        |         |         | ✔     |

## Rotas principais

| Verbo e rota                     | Ação                                |
|----------------------------------|-------------------------------------|
| `GET /`                          | Painel                              |
| `GET /chamados`                  | Lista com filtros                   |
| `GET /chamados/create`           | Formulário de abertura              |
| `POST /chamados`                 | Grava novo chamado                  |
| `GET /chamados/{chamado}`        | Detalhe + histórico + atendimento   |
| `PUT /chamados/{chamado}`        | Atualiza dados do chamado           |
| `PATCH /chamados/{chamado}/status` | Muda status / técnico + histórico |
| `DELETE /chamados/{chamado}`     | Exclui chamado                      |
| `/categorias`, `/usuarios`       | CRUD completo (admin)               |

Lista completa: `php artisan route:list --except-vendor`.

## Testes

```bash
php artisan test
```
