<?php

namespace App\Filament\Resources\ProductionOrderResource\Pages;

use App\Filament\Resources\ProductionOrderResource;
use App\Models\ProductionOrder;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductionOrder extends CreateRecord
{
    protected static string $resource = ProductionOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);
        $today = Carbon::now()->format('Ymd');
        $prefix = 'OP-' . $today . '-';

        $lastOrder = ProductionOrder::where('order_number', 'like', $prefix . '%')
            ->withTrashed()
            ->orderBy('order_number', 'desc')
            ->lockForUpdate()
            ->first();

        $nextSequence = 1;

        if ($lastOrder) {
            $lastSequence = (int)substr($lastOrder->order_number, strlen($prefix));
            $nextSequence = $lastSequence + 1;
        }

        $nextSequenceFormatted = str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);
        $data['order_number'] = $prefix . $nextSequenceFormatted;

        if (Auth::check()) { // Verifica se há um usuário logado
            $data['user_uuid'] = Auth::id(); // Atribui o UUID do usuário logado
        }

        return $data;
    }
}


