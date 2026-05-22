<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransparencyChartController extends Controller
{
    private const MONTH_LABELS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
    ];

    public function show(Request $request): JsonResponse
    {
        $currentYear = (int) now()->format('Y');
        $year = (int) $request->input('year', $currentYear);

        if ($year < $currentYear - 4 || $year > $currentYear) {
            $year = $currentYear;
        }

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = [
                'month' => $month,
                'label' => self::MONTH_LABELS[$month],
                'received' => 0.0,
                'spent' => 0.0,
            ];
        }

        Donation::query()
            ->whereIn('status', ['completed', 'success'])
            ->whereYear('created_at', $year)
            ->get(['amount', 'created_at'])
            ->each(function (Donation $donation) use (&$months) {
                if (! $donation->created_at) {
                    return;
                }

                $month = (int) $donation->created_at->format('n');
                $amount = (float) $donation->amount;

                $months[$month]['received'] += $amount;
                $months[$month]['spent'] += $amount;
            });

        $monthly = array_values(array_map(function (array $item) {
            return [
                'month' => $item['month'],
                'label' => $item['label'],
                'received' => round($item['received'], 2),
                'spent' => round($item['spent'], 2),
            ];
        }, $months));

        $maxValue = max(
            1,
            ...array_map(
                fn (array $item) => max($item['received'], $item['spent']),
                $monthly
            )
        );

        return response()->json([
            'data' => [
                'year' => $year,
                'months' => $monthly,
                'max_value' => $maxValue,
            ],
        ]);
    }
}
