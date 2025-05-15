<?php

namespace App\Filament\Resources\ChartOfAccountResource\Pages;

use App\Filament\Resources\ChartOfAccountResource;
use App\Models\ChartOfAccount; // Modelo correto
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth; // Para obter o company_id

class CreateChartOfAccount extends CreateRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $companyId = Auth::user()->company_id;
        if (!$companyId) {
            // Lançar uma exceção ou notificação se o usuário não tiver empresa
            // Isso não deveria acontecer se o TenantScope estiver funcionando corretamente
            // ou se o acesso ao resource for restrito.
            throw new \Exception("Usuário não associado a uma empresa.");
        }
        $data['company_id'] = $companyId;

        $parentUuid = $data['parent_uuid'] ?? null;
        $radical = '';

        if ($parentUuid) {
            $parentAccount = ChartOfAccount::where('uuid', $parentUuid)
                ->where('company_id', $companyId) // Garante que a conta pai seja da mesma empresa
                ->first();

            if ($parentAccount) {
                $radical = $parentAccount->code . '.';
                $query = ChartOfAccount::query()
                    ->where('company_id', $companyId)
                    ->where('parent_uuid', $parentUuid);
            } else {
                // Conta pai não encontrada ou não pertence à empresa, tratar como conta raiz
                // ou lançar erro. Por segurança, trataremos como raiz para evitar códigos incorretos.
                // Idealmente, o select do formulário já deveria filtrar isso.
                $query = ChartOfAccount::query()
                    ->where('company_id', $companyId)
                    ->whereNull('parent_uuid');
            }
        } else {
            // Conta raiz (sem pai)
            $query = ChartOfAccount::query()
                ->where('company_id', $companyId)
                ->whereNull('parent_uuid');
        }

        // Encontra o maior código numérico no último nível para esta empresa e pai
        $lastSibling = $query->selectRaw('MAX(CAST(SUBSTRING_INDEX(code, \'.\', -1) AS UNSIGNED)) as max_last_segment')
            ->first();

        $nextSegment = 1;
        if ($lastSibling && $lastSibling->max_last_segment !== null) {
            $nextSegment = (int)$lastSibling->max_last_segment + 1;
        }

        $data['code'] = $radical . $nextSegment;

        return $data; // Não chame parent::mutateFormDataBeforeCreate($data) aqui
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Conta contábil criada com sucesso!';
    }
}
