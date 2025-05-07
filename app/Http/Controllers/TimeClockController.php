<?php

namespace App\Http\Controllers;

use App\Models\TimeClockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Para validação
use Carbon\Carbon;

class TimeClockController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'actionType' => ['required', 'string', 'in:clock_in,clock_out,start_break,end_break'],
        ]);

        if ($validator->fails()) {
            // Se estiver fazendo uma requisição AJAX, você retornaria JSON
            // Para um formulário simples, pode redirecionar com erros
            return redirect()->route('time-clock.map-register-point', ['actionType' => $request->input('actionType', 'clock_in')])
                ->withErrors($validator)
                ->withInput()
                ->with('error_message', 'Dados inválidos. Verifique sua localização.'); // Mensagem genérica
        }

        $user = Auth::user();
        if (!$user || !$user->company_id) {
            // Adicionar mensagem de erro e redirecionar
            return redirect()->route('filament.app.pages.home-page') // Ou sua home
            ->with('error', 'Usuário ou empresa não configurados corretamente.');
        }

        try {
            TimeClockEntry::create([
                'user_id' => $user->uuid,
                'company_id' => $user->company_id,
                'recorded_at' => Carbon::now(),
                'type' => $request->input('actionType'),
                'status' => TimeClockEntry::STATUS_NORMAL, // Status inicial
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                // 'notes' => null, // Pode ser adicionado depois se necessário
            ]);

            // Adicionar lógica para verificar se a batida gera um 'alerta' aqui ou via Observer

            return redirect()->route('filament.app.pages.home-page') // Ou sua home
            ->with('message', 'Ponto registrado com sucesso!');

        } catch (\Exception $e) {
            // Log::error("Erro ao registrar ponto: " . $e->getMessage());
            return redirect()->route('filament.app.pages.home-page') // Ou sua home
            ->with('error', 'Ocorreu um erro ao registrar o ponto. Tente novamente.');
        }
    }
}
