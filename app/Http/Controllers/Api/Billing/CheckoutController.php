<?php

namespace App\Http\Controllers\Api\Billing;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function providers(): JsonResponse
    {
        $providers = collect(['paynet', 'uzumbank'])
            ->map(function (string $provider) {
                $config = config("payments.{$provider}", []);
                return [
                    'key' => $provider,
                    'enabled' => (bool) ($config['enabled'] ?? false),
                    'min_amount' => $config['min_amount'] ?? null,
                    'max_amount' => $config['max_amount'] ?? null,
                ];
            })
            ->values();

        return response()->json(['data' => $providers]);
    }

    public function init(Request $request): JsonResponse
    {
        $data = $request->validate([
            'case_id' => ['nullable', 'integer'],
            'service_type' => ['required', 'string'],
            'donor_name' => ['required', 'string', 'max:255'],
            'donor_email' => ['required', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'currency' => ['required', 'string', 'max:10'],
            'type' => ['required', 'string', 'in:one_time,monthly'],
            'message' => ['nullable', 'string'],
            'is_anonymous' => ['nullable', 'boolean'],
            'payment_method' => ['required', 'string', 'in:paycom,click,paynet,uzumbank'],
        ]);

        $amount = (float) $data['amount'];
        $provider = $data['payment_method'];

        $donation = Donation::create([
            'case_id' => $data['case_id'] ?? null,
            'service_type' => $data['service_type'],
            'donor_name' => $data['donor_name'],
            'donor_email' => $data['donor_email'],
            'amount' => $amount,
            'currency' => 'UZS',
            'type' => $data['type'],
            'message' => $data['message'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => 'pending',
        ]);

        $transactionId = $provider === 'paycom'
            ? 'PM_' . strtoupper(Str::random(12))
            : (string) random_int(1000000000, 9999999999);

        $payment = Payment::create([
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'status' => Payment::STATUS_PENDING,
            'payer_reference' => (string) $donation->id,
            'amount' => $amount,
            'currency' => 'UZS',
            'donation_id' => $donation->id,
            'live_mode' => true,
            'payload' => [
                'donor_name' => $data['donor_name'],
                'donor_email' => $data['donor_email'],
                'service_type' => $data['service_type'],
                'case_id' => $data['case_id'] ?? null,
                'type' => $data['type'],
                'message' => $data['message'] ?? null,
                'is_anonymous' => $data['is_anonymous'] ?? false,
            ],
        ]);

        $checkoutUrl = $this->buildCheckoutUrl($payment);

        Log::info('CHECKOUT INIT RESULT', [
            'provider' => $provider,
            'payment_id' => $payment->id,
            'donation_id' => $donation->id,
            'transaction_id' => $payment->transaction_id,
            'amount_uzs' => $amount,
            'checkout_url' => $checkoutUrl,
        ]);

        return response()->json([
            'data' => [
                'donation_id' => $donation->id,
                'payment_id' => $payment->id,
                'provider' => $provider,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'checkout_url' => $checkoutUrl,
            ],
        ], 201);
    }

    protected function buildCheckoutUrl(Payment $payment): ?string
    {
        if ($payment->provider === 'paycom') {
            $merchantId = config('payments.paycom.merchant_id');

            if (!$merchantId) {
                return null;
            }

            $amountTiyin = (int) round($payment->amount * 100);

            return 'https://payme.uz/fallback/merchant/'
                . '?id=' . urlencode((string) $merchantId)
                . '&amount=' . $amountTiyin
                . '&ac.user_data=' . urlencode((string) $payment->donation_id);
        }

        if ($payment->provider === 'click') {
            $serviceId = config('payments.click.service_id');
            $merchantId = config('payments.click.merchant_id');
            $returnUrl = config('payments.click.return_url', url('/'));

            if (!$serviceId || !$merchantId) {
                return null;
            }

            return 'https://my.click.uz/services/pay/'
                . '?service_id=' . urlencode((string) $serviceId)
                . '&merchant_id=' . urlencode((string) $merchantId)
                . '&amount=' . urlencode((string) ((int) round($payment->amount)))
                . '&transaction_param=' . urlencode((string) $payment->donation_id)
                . '&return_url=' . urlencode((string) $returnUrl);
        }

        if ($payment->provider === 'paynet') {
    		$merchantId = config('payments.paynet.app_merchant_id', '4590');
    		return 'https://app.paynet.uz/?m=' . urlencode((string) $merchantId);
	}
        if ($payment->provider === 'uzumbank') {
            return 'https://www.apelsin.uz/open-service?serviceId=12030307';
        }

        return null;
    }
}
