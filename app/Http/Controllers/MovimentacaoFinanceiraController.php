<?php

namespace App\Http\Controllers;

use App\Models\MovimentacaoFinanceira;
use App\Models\PlanoDeConta;
use App\Models\RegistroDePonto;
use App\Models\User;
use DateTime;
use Illuminate\Support\Number;

class MovimentacaoFinanceiraController extends Controller
{
    public static function atualizarSaldo(MovimentacaoFinanceira $movimentacao)
    {
        $diff = $movimentacao->natureza === 'credito' ? $movimentacao->valor : (-1 * $movimentacao->valor);
        do{
            $planoDeConta = PlanoDeConta::query()->find($movimentacao->plano_de_conta_id);
            $valorRealizado = $planoDeConta->valor_realizado ?? 0;
            $planoDeConta->update(['valor_realizado' => $valorRealizado + $diff]);

            $movimentacao->plano_de_conta_id = $planoDeConta->plano_de_conta_id;
        }while($planoDeConta->plano_de_conta_id);
    }
}
