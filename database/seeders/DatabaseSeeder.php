<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = collect([
            ['nome' => 'Service Desk', 'descricao' => 'Primeiro atendimento: recebe e classifica todos os incidentes.'],
            ['nome' => 'Infraestrutura', 'descricao' => 'Computadores, impressoras, servidores e periféricos.'],
            ['nome' => 'Redes e Telecom', 'descricao' => 'Wi-Fi, cabeamento, VPN e telefonia.'],
        ])->mapWithKeys(fn ($g) => [$g['nome'] => Grupo::create($g)]);

        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@helpdesk.test',
            'password' => 'senha123',
            'perfil' => 'admin',
            'setor' => 'TI',
            'grupo_id' => $grupos['Service Desk']->id,
        ]);

        User::create([
            'name' => 'Antonio Lira',
            'email' => 'antonio@helpdesk.test',
            'password' => 'senha123',
            'perfil' => 'admin',
            'setor' => 'TI',
            'grupo_id' => $grupos['Service Desk']->id,
        ]);

        $tecnicos = collect([
            ['name' => 'Carlos Técnico', 'email' => 'carlos@helpdesk.test', 'grupo_id' => $grupos['Infraestrutura']->id],
            ['name' => 'Fernanda Suporte', 'email' => 'fernanda@helpdesk.test', 'grupo_id' => $grupos['Service Desk']->id],
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

        $grupoPorCategoria = [
            'Hardware' => 'Infraestrutura',
            'Rede e Internet' => 'Redes e Telecom',
        ];

        // [título, descrição, categoria, impacto, urgência, status, nota de trabalho]
        $exemplos = [
            ['Impressora do 2º andar não imprime', 'A impressora mostra "papel atolado", mas não há papel preso.', 'Hardware', 2, 2, 'em_andamento', 'Sensor de papel sujo. Vou levar kit de limpeza.'],
            ['Instalar pacote Office', 'Preciso do Excel instalado no novo notebook do setor.', 'Software', 3, 2, 'aberto', null],
            ['Wi-Fi caindo na sala de reuniões', 'A conexão cai a cada 10 minutos durante as reuniões. Afeta toda a diretoria.', 'Rede e Internet', 1, 1, 'aberto', null],
            ['Senha do sistema bloqueada', 'Errei a senha 3 vezes e o sistema bloqueou meu acesso.', 'Acesso e Senhas', 3, 1, 'resolvido', 'Conta desbloqueada no AD e senha temporária enviada.'],
            ['Caixa de e-mail cheia', 'Recebo aviso de caixa cheia e não consigo enviar mensagens.', 'E-mail', 3, 2, 'aguardando', 'Aguardando usuária liberar horário para arquivar mensagens antigas.'],
            ['Monitor piscando', 'O monitor secundário pisca de vez em quando.', 'Hardware', 3, 3, 'fechado', 'Cabo HDMI substituído.'],
            ['VPN não conecta em home office', 'Erro de autenticação ao conectar na VPN. Toda a equipe comercial está sem acesso.', 'Rede e Internet', 1, 2, 'aberto', null],
            ['Atualizar antivírus', 'O antivírus está pedindo atualização há dias.', 'Software', 2, 3, 'em_andamento', 'Atualização agendada para o fim do expediente.'],
        ];

        foreach ($exemplos as $i => [$titulo, $descricao, $categoria, $impacto, $urgencia, $status, $nota]) {
            $solicitante = $usuarios[$i % $usuarios->count()];
            $grupo = $grupos[$grupoPorCategoria[$categoria] ?? 'Service Desk'];
            $tecnico = $status === 'aberto' ? null : ($tecnicos->firstWhere('grupo_id', $grupo->id) ?? $tecnicos[1]);

            $chamado = Chamado::create([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'categoria_id' => $categorias->firstWhere('nome', $categoria)->id,
                'grupo_id' => $grupo->id,
                'impacto' => $impacto,
                'urgencia' => $urgencia,
                'status' => $status,
                'solicitante_id' => $solicitante->id,
                'tecnico_id' => $tecnico?->id,
                'resolvido_em' => in_array($status, Chamado::FINALIZADOS) ? now() : null,
            ]);

            $chamado->historicos()->create([
                'user_id' => $solicitante->id,
                'tipo' => 'comentario',
                'status_novo' => 'aberto',
                'comentario' => 'Incidente registrado.',
            ]);

            if ($tecnico) {
                $chamado->historicos()->create([
                    'user_id' => $tecnico->id,
                    'tipo' => 'comentario',
                    'status_anterior' => 'aberto',
                    'status_novo' => $status,
                    'comentario' => "Grupo de atribuição: {$grupo->nome}. Atribuído a: {$tecnico->name}.",
                ]);
            }

            if ($nota) {
                $chamado->historicos()->create([
                    'user_id' => $tecnico->id,
                    'tipo' => 'nota',
                    'status_anterior' => $status,
                    'status_novo' => $status,
                    'comentario' => $nota,
                ]);
            }
        }
    }
}
