<?php

namespace App\Console\Commands;

use App\Models\FinancialPayment;
use App\Services\Finance\PaymentService;
use Illuminate\Console\Command;
use Throwable;

class RepairFinancialPaymentCashMovements extends Command
{
    protected $signature = 'finance:repair-payment-cash-movements {--payment=}';
    protected $description = 'Cria movimentações de caixa ausentes para pagamentos e estornos financeiros';

    public function handle(PaymentService $service): int
    {
        $query = FinancialPayment::query()
            ->with('payable');

        if (filled($this->option('payment'))) {
            $query->where('number', $this->option('payment'));
        }

        $created = 0;
        $failed = 0;

        $query->chunkById(100, function ($payments) use (
            $service,
            &$created,
            &$failed,
        ): void {
            foreach ($payments as $payment) {
                try {
                    if ($payment->status === 'reversed') {
                        $before = $payment->cashMovements()
                            ->where('category', 'payment_reversal')
                            ->count();

                        $service->postReversalCashMovement(
                            $payment,
                            $payment->reversal_reason
                                ?: 'Estorno regularizado automaticamente.'
                        );

                        $after = $payment->cashMovements()
                            ->where('category', 'payment_reversal')
                            ->count();

                        $created += max(0, $after - $before);
                    }

                    $before = $payment->cashMovements()
                        ->where('category', 'payment')
                        ->count();

                    $service->postPaymentCashMovement(
                        $payment,
                        $payment->payable
                    );

                    $after = $payment->cashMovements()
                        ->where('category', 'payment')
                        ->count();

                    $created += max(0, $after - $before);
                } catch (Throwable $exception) {
                    $failed++;

                    $this->warn(
                        "{$payment->number}: {$exception->getMessage()}"
                    );
                }
            }
        });

        $this->info("{$created} movimentação(ões) criada(s).");

        if ($failed > 0) {
            $this->warn("{$failed} pagamento(s) não puderam ser regularizados.");
        }

        return self::SUCCESS;
    }
}
