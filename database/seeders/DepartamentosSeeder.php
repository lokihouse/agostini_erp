<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class DepartamentosSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
