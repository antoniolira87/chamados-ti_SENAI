<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@helpdesk.test',
            'password' => 'senha123',
            'perfil' => 'admin',
            'setor' => 'TI',
        ]);

        $tecnicos = collect([
            ['name' => 'Carlos Técnico', 'email' => 'carlos@helpdesk.test'],
            ['name' => 'Fernanda Suporte', 'email' => 'fernanda@helpdesk.test'],
        ])->map(fn ($t) => User::create($t + ['password' => 'senha123', 'perfil' => 'tecnico', 'setor' => 'TI']));

        $usuarios = collect([
            ['name' => 'Maria Silva', 'email' => 'maria@helpdesk.test', 'setor' => 'Financeiro'],
            ['name' => 'João Souza', 'email' => 'joao@helpdesk.test', 'setor' => 'RH'],
            ['name' => 'Ana Lima', 'email' => 'ana@helpdesk.test', 'setor' => 'Comercial'],
        ])->map(fn ($u) => User::create($u + ['password' => 'senha123', 'perfil' => 'usuario']));

        $categorias = collect([
            ['nome' => 'Hardware', 'descricao' => 'Computadores, monitores, periféricos e impressoras.'],
            ['nome' => 'Software', 'descricao' => 'Instalação, atualização e erros em programas.'],
            ['nome' => 'Rede e Internet', 'descricao' => 'Wi-Fi, cabeamento, VPN e lentidão de conexão.'],
            ['nome' => 'Acesso e Senhas', 'descricao' => 'Criação de contas, bloqueios e redefinição de senha.'],
            ['nome' => 'E-mail', 'descricao' => 'Problemas com caixa de e-mail e calendário.'],
        ])->map(fn ($c) => Categoria::create($c));

        $exemplos = [
            ['Impressora do 2º andar não imprime', 'A impressora mostra "papel atolado", mas não há papel preso.', 'Hardware', 'alta', 'em_andamento'],
            ['Instalar pacote Office', 'Preciso do Excel instalado no novo notebook do setor.', 'Software', 'media', 'aberto'],
            ['Wi-Fi caindo na sala de reuniões', 'A conexão cai a cada 10 minutos durante as reuniões.', 'Rede e Internet', 'urgente', 'aberto'],
            ['Senha do sistema bloqueada', 'Errei a senha 3 vezes e o sistema bloqueou meu acesso.', 'Acesso e Senhas', 'alta', 'resolvido'],
            ['Caixa de e-mail cheia', 'Recebo aviso de caixa cheia e não consigo enviar mensagens.', 'E-mail', 'media', 'aguardando'],
            ['Monitor piscando', 'O monitor secundário pisca de vez em quando.', 'Hardware', 'baixa', 'fechado'],
            ['VPN não conecta em home office', 'Erro de autenticação ao conectar na VPN.', 'Rede e Internet', 'alta', 'aberto'],
            ['Atualizar antivírus', 'O antivírus está pedindo atualização há dias.', 'Software', 'baixa', 'em_andamento'],
        ];

        foreach ($exemplos as $i => [$titulo, $descricao, $categoria, $prioridade, $status]) {
            $solicitante = $usuarios[$i % $usuarios->count()];
            $tecnico = $status === 'aberto' ? null : $tecnicos[$i % $tecnicos->count()];

            $chamado = Chamado::create([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'categoria_id' => $categorias->firstWhere('nome', $categoria)->id,
                'prioridade' => $prioridade,
                'status' => $status,
                'solicitante_id' => $solicitante->id,
                'tecnico_id' => $tecnico?->id,
                'resolvido_em' => in_array($status, ['resolvido', 'fechado']) ? now() : null,
            ]);

            $chamado->historicos()->create([
                'user_id' => $solicitante->id,
                'status_novo' => 'aberto',
                'comentario' => 'Chamado aberto.',
            ]);

            if ($status !== 'aberto') {
                $chamado->historicos()->create([
                    'user_id' => $tecnico->id,
                    'status_anterior' => 'aberto',
                    'status_novo' => $status,
                    'comentario' => "Técnico responsável: {$tecnico->name}.",
                ]);
            }
        }
    }
}
