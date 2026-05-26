<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PaymentService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Payment::query()
            ->onlineOnly()
            ->latest()
            ->with('donation');

        $query->when($request->filled('provider'), function ($q) use ($request) {
            $q->where('provider', $request->string('provider'));
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->string('status'));
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->string('date_from'));
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->string('date_to'));
        });

        $query->when($request->filled('search') || $request->filled('q'), function ($q) use ($request) {
            $term = '%' . ($request->string('search') ?: $request->string('q')) . '%';

            $q->where(function ($inner) use ($term) {
                $inner->where('transaction_id', 'like', $term)
                    ->orWhere('payer_reference', 'like', $term)
                    ->orWhere('provider', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('external_id', 'like', $term)
                    ->orWhereHas('donation', function ($donationQuery) use ($term) {
                        $donationQuery->where('donor_name', 'like', $term);
                    })
                    ->orWhereRaw("LOWER(CAST(payload AS CHAR)) LIKE ?", [strtolower($term)]);
            });
        });

        return $query->paginate(20);
    }

    public function findOrFail(int $id): Payment
    {
        return Payment::with('donation')->findOrFail($id);
    }

    public function update(Payment $payment, array $payload): Payment
    {
        $payment->update($payload);

        return $payment->fresh();
    }
}
