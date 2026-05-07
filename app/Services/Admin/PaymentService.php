<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class PaymentService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = Payment::query()->latest();

        if ($request->filled('provider')) {
            $query->where('provider', $request->string('provider'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query->paginate((int) $request->input('per_page', 20));
    }

    public function findOrFail(int $id): Payment
    {
        return Payment::query()->findOrFail($id);
    }

    public function update(Payment $payment, array $payload): Payment
    {
        $payment->update($payload);
        return $payment->fresh();
    }
}
