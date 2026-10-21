<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Models\Rental;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Chronological history of a rental (advanced feature of the rental module):
 * reserved, paid, rented, contract signed, extended, overdue, returned...
 *
 * It gathers dates that already exist in the database (reservation, payments, invoice,
 * rental, contract, extensions, inspections) and sorts them in one list.
 * Nothing is stored: the timeline is always computed from the real records.
 */
class RentalTimelineService
{
    /**
     * @return Collection<int, array{at: Carbon, title: string, detail: ?string, type: string, planned: bool}>
     *                                                                                                         type is one of: success, info, warning, error. "planned" is true for future dates.
     */
    public function events(Rental $rental): Collection
    {
        $rental->loadMissing(['contract', 'extensions', 'inspections']);
        $events = collect();

        // Small helper that adds one event (ignored when the date is missing)
        $add = function (mixed $date, string $title, ?string $detail = null, string $type = 'info') use ($events): void {
            $at = $this->toCarbon($date);
            if (! $at) {
                return;
            }
            $events->push([
                'at' => $at,
                'title' => $title,
                'detail' => $detail,
                'type' => $type,
                'planned' => $at->isFuture(),
            ]);
        };

        // 1) Reservation, payments and invoice (module of Student 4)
        $reservation = $rental->reservation_id
            ? Reservation::with(['payments', 'invoice'])->find($rental->reservation_id)
            : null;

        if ($reservation) {
            $add($reservation->created_at, "Reservation {$reservation->reference} requested");

            foreach ($reservation->payments as $payment) {
                $amount = number_format((float) $payment->amount, 2).' TND';
                if ($payment->status === 'paid') {
                    $add($payment->payment_date ?? $payment->created_at, "Payment of {$amount} received", (string) $payment->payment_method, 'success');
                } else {
                    $add($payment->created_at, "Payment method recorded ({$amount})", 'Waiting for verification', 'warning');
                }
            }

            if ($reservation->invoice) {
                $add($reservation->invoice->issue_date ?? $reservation->invoice->created_at, "Invoice {$reservation->invoice->invoice_number} issued");
            }
        }

        // 2) The rental itself
        $add($rental->created_at, "Rental {$rental->reference} created");
        $add($rental->start_date, 'Rental period starts');

        // 3) The contract
        if ($rental->contract) {
            $contract = $rental->contract;
            $add($contract->created_at, "Contract {$contract->contract_number} generated");

            if ($contract->signed_at) {
                $add($contract->signed_at, 'Contract signed by the renter', null, 'success');
            } elseif ($contract->contract_status === ContractStatus::TERMINATED) {
                $add($contract->updated_at, 'Contract declined by the renter', null, 'error');
            }
        }

        // 4) Extension requests
        foreach ($rental->extensions as $extension) {
            $until = $extension->new_end_date->format('d/m/Y');
            $add($extension->created_at ?? $extension->requested_date, "Extension requested until {$until}", $extension->reason);

            if ($extension->status === ExtensionStatus::APPROVED) {
                $add($extension->updated_at, "Extension approved until {$until}", '+'.number_format((float) $extension->additional_amount, 2).' TND', 'success');
            } elseif ($extension->status === ExtensionStatus::REJECTED) {
                $add($extension->updated_at, 'Extension rejected', null, 'error');
            }
        }

        // 5) The end of the rental, and the lateness
        $add($rental->end_date, $rental->end_date->isFuture() ? 'Planned end of the rental' : 'End of the rental');

        if ($rental->status === RentalStatus::ACTIVE && $rental->end_date->lt(today())) {
            $add($rental->end_date->copy()->addDay(), 'Rental became overdue', 'Late fees are charged by the day', 'warning');
        }

        // 6) Return and inspection
        foreach ($rental->inspections as $inspection) {
            $damaged = (bool) $inspection->damage_detected;
            $add(
                $inspection->inspection_date ?? $inspection->created_at,
                $damaged ? 'Equipment returned with damage' : 'Equipment returned and inspected',
                $inspection->comments,
                $damaged ? 'error' : 'success'
            );
        }

        // 7) Final status
        if ($rental->status === RentalStatus::COMPLETED) {
            $add($rental->updated_at, 'Rental completed', null, 'success');
        } elseif ($rental->status === RentalStatus::CANCELLED) {
            $add($rental->updated_at, 'Rental cancelled', null, 'error');
        }

        // Oldest first
        return $events->sortBy(fn (array $event) => $event['at']->timestamp)->values();
    }

    /** Turns a date, a date string or null into a Carbon (or null). */
    private function toCarbon(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }
}
