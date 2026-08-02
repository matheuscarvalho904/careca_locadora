<?php

namespace App\Services\Finance;

use App\Models\CashMovement;
use App\Models\FinancialAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class TreasuryService
{
    public function supply(FinancialAccount $account, float $value, string $description, array $data = []): CashMovement
    {
        return $this->manual($account, 'entry', 'supply', $value, $description, $data);
    }

    public function withdrawal(FinancialAccount $account, float $value, string $description, array $data = []): CashMovement
    {
        return $this->manual($account, 'exit', 'withdrawal', $value, $description, $data);
    }

    public function transfer(FinancialAccount $from, FinancialAccount $to, float $value, array $data = []): array
    {
        if ($from->is($to)) {
            throw ValidationException::withMessages(['destination' => 'A conta de destino deve ser diferente da origem.']);
        }

        if ($value <= 0) {
            throw ValidationException::withMessages(['value' => 'Informe um valor maior que zero.']);
        }

        if ($from->organization_id !== $to->organization_id) {
            throw ValidationException::withMessages(['destination' => 'As contas devem pertencer à mesma organização.']);
        }

        if ($from->current_balance < $value) {
            throw ValidationException::withMessages(['value' => 'Saldo insuficiente na conta de origem.']);
        }

        return DB::transaction(function () use ($from, $to, $value, $data): array {
            $group = (string) Str::uuid();
            $at = $data['occurred_at'] ?? now();
            $notes = $data['notes'] ?? null;

            $exit = CashMovement::query()->create([
                'organization_id'=>$from->organization_id,
                'financial_account_id'=>$from->id,
                'type'=>'exit','category'=>'transfer','status'=>'posted',
                'reconciliation_status'=>'pending','occurred_at'=>$at,'value'=>$value,
                'description'=>"Transferência para {$to->name}",'notes'=>$notes,
                'transfer_group_id'=>$group,
            ]);

            $entry = CashMovement::query()->create([
                'organization_id'=>$to->organization_id,
                'financial_account_id'=>$to->id,
                'type'=>'entry','category'=>'transfer','status'=>'posted',
                'reconciliation_status'=>'pending','occurred_at'=>$at,'value'=>$value,
                'description'=>"Transferência de {$from->name}",'notes'=>$notes,
                'transfer_group_id'=>$group,
            ]);

            return compact('exit','entry');
        });
    }

    public function reconcile(CashMovement $movement): CashMovement
    {
        $movement->update([
            'reconciliation_status'=>'reconciled',
            'reconciled_at'=>now(),
            'reconciled_by'=>auth()->id(),
        ]);

        return $movement->fresh();
    }

    public function markDivergent(CashMovement $movement, string $reason): CashMovement
    {
        $movement->update([
            'reconciliation_status'=>'divergent',
            'notes'=>trim(implode(PHP_EOL, array_filter([$movement->notes, "Divergência: {$reason}"]))),
        ]);

        return $movement->fresh();
    }

    private function manual(FinancialAccount $account, string $type, string $category, float $value, string $description, array $data): CashMovement
    {
        if ($value <= 0) {
            throw ValidationException::withMessages(['value'=>'Informe um valor maior que zero.']);
        }

        if ($type === 'exit' && $account->current_balance < $value) {
            throw ValidationException::withMessages(['value'=>'Saldo insuficiente para esta saída.']);
        }

        return CashMovement::query()->create([
            'organization_id'=>$account->organization_id,
            'financial_account_id'=>$account->id,
            'type'=>$type,'category'=>$category,'status'=>'posted',
            'reconciliation_status'=>'pending',
            'occurred_at'=>$data['occurred_at'] ?? now(),
            'value'=>$value,'description'=>$description,
            'notes'=>$data['notes'] ?? null,
        ]);
    }
}
