<?php

namespace App\Console\Commands;

use App\Services\Rentals\RentalAvailabilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditRentalConflicts extends Command
{
    protected $signature = 'rentals:audit-conflicts';
    protected $description = 'Lista reservas conflitantes do mesmo ativo';

    public function handle(): int
    {
        $statuses = implode("','", RentalAvailabilityService::BLOCKING_STATUSES);

        $rows = DB::select("
            select
                a.prefix,
                r1.number as reservation_1,
                i1.starts_at as start_1,
                i1.ends_at as end_1,
                r2.number as reservation_2,
                i2.starts_at as start_2,
                i2.ends_at as end_2
            from rental_reservation_items i1
            join rental_reservation_items i2
              on i1.asset_id = i2.asset_id
             and i1.id < i2.id
             and i1.starts_at < i2.ends_at
             and i1.ends_at > i2.starts_at
            join rental_reservations r1 on r1.id = i1.reservation_id
            join rental_reservations r2 on r2.id = i2.reservation_id
            join assets a on a.id = i1.asset_id
            where r1.status in ('{$statuses}')
              and r2.status in ('{$statuses}')
              and r1.deleted_at is null
              and r2.deleted_at is null
            order by a.prefix, i1.starts_at
        ");

        if ($rows === []) {
            $this->info('Nenhum conflito de reserva encontrado.');
            return self::SUCCESS;
        }

        $this->table(
            ['Ativo', 'Reserva 1', 'Início 1', 'Fim 1', 'Reserva 2', 'Início 2', 'Fim 2'],
            collect($rows)->map(fn ($row): array => [
                $row->prefix,
                $row->reservation_1,
                $row->start_1,
                $row->end_1,
                $row->reservation_2,
                $row->start_2,
                $row->end_2,
            ])->all()
        );

        $this->warn(count($rows) . ' conflito(s) encontrado(s).');
        return self::FAILURE;
    }
}
