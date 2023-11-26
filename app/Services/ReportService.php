<?php

namespace App\Services;

use App\Models\Payment;

readonly class ReportService
{
    /**
     * @param Payment[] $payments
     */
    public function generatePaymentReport(iterable $payments): array
    {
        $result = [
            'total' => 0.0,
        ];

        foreach ($payments as $payment) {
            $result['total'] += $payment->amount;
        }

        return $result;
    }
}
