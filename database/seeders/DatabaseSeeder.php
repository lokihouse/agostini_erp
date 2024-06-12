<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Produto;
use App\Models\User;
use App\Models\UserCliente;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('shield:generate --all');

        Role::create(['name' => 'gerente']);

        Empresa::create([
            'nome' => 'Empresa 1'
        ]);

        Empresa::create([
            'nome' => 'Empresa 2'
        ]);

        // -- DEPARTAMENTOS --

        $departamentos = [
            ["Compras", "Responsável por adquirir toda a matéria-prima e componentes necessários para a produção, desde madeira e ferragens até acabamentos e revestimentos."],
            ["Expedição e Recebimento", "Gerencia a entrada e saída de materiais, incluindo a inspeção de qualidade, armazenamento e organização do estoque."],
            ["Desenvolvimento de Produtos", "Cria e projeta novos móveis, considerando aspectos funcionais, estéticos, ergonômicos e de custo."],
            ["Engenharia", "Projeta e otimiza os processos produtivos, definindo métodos, ferramentas e layout da fábrica."],
            ["Produção - Recepção e análise do pedido", "Recebimento e análise do pedido do cliente, incluindo especificações do móvel, quantidade desejada e prazo de entrega."],
            ["Produção - Desenvolvimento do projeto", "Criação ou adaptação do projeto do móvel, de acordo com as necessidades do cliente."],
            ["Produção - Corte das peças", "Corte das peças de madeira e outros materiais com base no projeto."],
            ["Produção - Furação e usinagem", "Realização de furos, encaixes e outros detalhes nas peças."],
            ["Produção - Aplicação de bordas e acabamentos", "Aplicação de bordas, fitas de PVC, laminados e outros revestimentos nas peças."],
            ["Produção - Montagem", "União das peças e estruturação do móvel"],
            ["Produção - Aplicação de ferragens", "Instalação de dobradiças, puxadores, corrediças e outras ferragens."],
            ["Produção - Acabamento final", "Aplicação de tintas, vernizes, lacas e outros acabamentos para dar o toque final ao móvel."],
            ["Produção - Controle de qualidade", "Inspeção final do móvel para garantir que atenda aos padrões de qualidade exigidos."],
            ["Produção - Embalagem e expedição", "Embalagem cuidadosa do móvel para protegê-lo durante o transporte e envio para o cliente."],
            ["Logística", "Cuida do transporte dos móveis da fábrica para os distribuidores ou clientes finais."],
            ["Administração e Finanças", "Gerencia os recursos humanos, financeiros e administrativos da empresa."],
            ["Vendas", "Responsável por comercializar os móveis para clientes, seja diretamente ou através de lojas e revendedores."],
            ["Pós-venda", "Atende aos clientes após a compra, solucionando dúvidas, problemas e oferecendo suporte técnico."]
        ];

        foreach (Empresa::all() as $empresa){
            foreach ($departamentos as $departamento) {
                Departamento::create([
                    'empresa_id' => $empresa->id,
                    'nome' => $departamento[0],
                    'descricao' => $departamento[1]
                ]);
            }
        }

        User::create([
            'empresa_id' => 1,
            'name' => 'Root',
            'username' => 'root',
            'password' => Hash::make('password')
        ])
        ->syncRoles('super_admin');

        User::create([
            'empresa_id' => 1,
            'name' => 'Luiz',
            'username' => 'luizcarlos',
            'password' => Hash::make('password')
        ])
            ->syncRoles('super_admin');

        User::create([
            'empresa_id' => 1,
            'name' => 'Gerente',
            'username' => 'gerente1',
            'password' => Hash::make('password')
        ])
        ->syncRoles('gerente');

        User::create([
            'empresa_id' => 2,
            'name' => 'Gerente',
            'username' => 'gerente2',
            'password' => Hash::make('password')
        ])->syncRoles('gerente');

        User::create([
            'empresa_id' => 1,
            'name' => 'Vendedor 1',
            'username' => 'vendedor1',
            'password' => Hash::make('password')
        ])
        ->syncRoles('usuarios_base');

        User::create([
            'empresa_id' => 1,
            'name' => 'Vendedor 2',
            'username' => 'vendedor2',
            'password' => Hash::make('password')
        ])
        ->syncRoles('usuarios_base');

        Cliente::create([
            'empresa_id' => 1,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 1 ltda.',
            'nome_fantasia' => 'CliTest1',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.916667',
            'longitude' => '-43.933333',
        ]);

        Cliente::create([
            'empresa_id' => 1,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 2 ltda.',
            'nome_fantasia' => 'CliTest2',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.816667',
            'longitude' => '-43.833333',
        ]);

        Cliente::create([
            'empresa_id' => 2,
            'cnpj' => '00000000000000',
            'razao_social' => 'Cliente de teste 3 ltda.',
            'nome_fantasia' => 'CliTest3',
            'logradouro' => 'L',
            'numero' => 'N',
            'bairro' => 'B',
            'municipio' => 'M',
            'uf' => 'MG',
            'cep' => '30000000',
            'latitude' => '-19.916667',
            'longitude' => '-43.933333',
        ]);

        UserCliente::create([
            'empresa_id' => 1,
            'user_id' => 5,
            'cliente_id' => 1
        ]);

        UserCliente::create([
            'empresa_id' => 1,
            'user_id' => 5,
            'cliente_id' => 2
        ]);

        UserCliente::create([
            'empresa_id' => 1,
            'user_id' => 6,
            'cliente_id' => 1
        ]);

        for($i=0; $i<30; $i++){
            Visita::create([
                'empresa_id' => 1,
                'cliente_id' => 1,
                'data' => Carbon::make('today')->addDays($i)->format('Y-m-d'),
                'status' => 'agendada'
            ]);

            Visita::create([
                'empresa_id' => 1,
                'cliente_id' => 2,
                'data' => Carbon::make('today')->addDays($i)->format('Y-m-d'),
                'status' => 'agendada'
            ]);

            Visita::create([
                'empresa_id' => 2,
                'cliente_id' => 3,
                'data' => Carbon::make('today')->addDays($i)->format('Y-m-d'),
                'status' => 'agendada'
            ]);
        }
    }
}
