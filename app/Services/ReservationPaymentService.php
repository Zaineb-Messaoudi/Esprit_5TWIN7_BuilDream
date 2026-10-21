<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Models\Reservation;
use Illuminate\Support\Str;
use RuntimeException;

class ReservationPaymentService
{
    public function totalWithTax(Reservation $reservation): float
    {
        $invoice = $reservation->relationLoaded('invoice')
            ? $reservation->getRelation('invoice')
            : $reservation->invoice()->first();

        if ($invoice) {
            return (float) $invoice->total;
        }

        return round((float) $reservation->total_amount * (1 + (float) config('billing.tax_rate', 0.19)), 2);
    }

    public function amountPaid(Reservation $reservation): float
    {
        return round((float) $reservation->payments()
            ->whereIn('status', ['paid', 'partially_refunded'])
            ->selectRaw('COALESCE(SUM(amount - refunded_amount), 0) as net_paid')
            ->value('net_paid'), 2);
    }

    public function remainingAmount(Reservation $reservation): float
    {
        return max(0, round($this->totalWithTax($reservation) - $this->amountPaid($reservation), 2));
    }

    public function nextPaymentAmount(Reservation $reservation): float
    {
        $pendingPayment = $reservation->payments()->where('status', 'pending')->latest()->first();
        if ($pendingPayment) {
            return round((float) $pendingPayment->amount, 2);
        }

        $remaining = $this->remainingAmount($reservation);
        if ($remaining <= 0) {
            return 0;
        }

        if ($this->amountPaid($reservation) > 0) {
            return $remaining;
        }

        return $reservation->start_date->lte(today()->addDays(7))
            ? $remaining
            : round($this->totalWithTax($reservation) * 0.30, 2);
    }

    public function nextPaymentStage(Reservation $reservation): string
    {
        $pendingPayment = $reservation->payments()->where('status', 'pending')->latest()->first();
        if ($pendingPayment) {
            return $pendingPayment->payment_stage;
        }

        if ($this->amountPaid($reservation) > 0) {
            return 'balance';
        }

        return $this->nextPaymentAmount($reservation) >= $this->totalWithTax($reservation)
            ? 'full'
            : 'deposit';
    }

    public function nextPaymentDueDate(Reservation $reservation): string
    {
        $pendingPayment = $reservation->payments()->where('status', 'pending')->latest()->first();
        if ($pendingPayment) {
            return $pendingPayment->due_date?->toDateString() ?? today()->toDateString();
        }

        return $this->nextPaymentStage($reservation) === 'balance'
            ? $reservation->start_date->copy()->subDays(7)->toDateString()
            : today()->toDateString();
    }

    public function synchronizeAfterPayment(Reservation $reservation): bool
    {
        if ($reservation->status !== 'confirmed') {
            throw new RuntimeException('The reservation must be approved before a payment can be verified.');
        }

        $total = $this->totalWithTax($reservation);
        $paid = $this->amountPaid($reservation);

        if ($paid > $total) {
            throw new RuntimeException('Verified reservation payments exceed the amount due.');
        }

        $isFullyPaid = $paid >= $total;
        $invoice = Invoice::firstOrNew(['reservation_id' => $reservation->id]);
        $invoiceData = [
            'invoice_number' => $invoice->invoice_number ?: 'INV-'.strtoupper(Str::random(10)),
            'issue_date' => $invoice->issue_date ?: today()->toDateString(),
            'status' => $isFullyPaid ? 'paid' : 'partially_paid',
        ];

        if (! $invoice->exists) {
            $invoiceData += [
                'subtotal' => $reservation->total_amount,
                'tax' => round((float) $reservation->total_amount * (float) config('billing.tax_rate', 0.19), 2),
                'total' => $total,
            ];
        }

        $invoice->fill($invoiceData)->save();

        if (! $isFullyPaid) {
            return false;
        }

        $rental = Rental::firstOrCreate(
            ['reservation_id' => $reservation->id],
            [
                'reference' => 'RNT-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'equipment_id' => $reservation->equipment_id,
                'user_id' => $reservation->user_id,
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'total_amount' => $total,
                'status' => 'pending',
            ],
        );
        $rental->update(['total_amount' => $total]);

        RentalContract::firstOrCreate(
            ['rental_id' => $rental->id],
            [
                'contract_number' => 'CTR-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'terms' => __('The renter agrees to use the equipment safely and return it in the same condition by the agreed end date.'),
                'deposit_amount' => 0,
                'contract_status' => 'draft',
            ],
        );

        return true;
    }
}
