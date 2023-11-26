<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentReportRequest;
use App\Services\PaymentService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

use function response;

class ReportController extends BaseController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly ReportService $reportService,
    ) {}

    public function generatePaymentReport(PaymentReportRequest $request): JsonResponse
    {
        $payments = $this->paymentService->getByDatePeriod($request->merchantId, $request->from, $request->to);
        $report = $this->reportService->generatePaymentReport($payments);

        return response()->json($report);
    }
}
