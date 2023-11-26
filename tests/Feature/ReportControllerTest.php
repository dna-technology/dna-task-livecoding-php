<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\AccountService;
use App\Services\MerchantService;
use App\Services\PaymentService;
use App\Services\UserService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private AccountService $accountService;
    private MerchantService $merchantService;
    private PaymentService $paymentService;
    private UserService $userService;

    public static function invalidDateProvider(): array
    {
        return [
            ['2023-11-15 00:00:00', '2023-11-17 00:00:00.000'],
            ['2023-11-15 00:00:00', '2023-11-17'],
            ['2023-11-15 00:00:00.000', '2023-11-17 00:00:00'],
            ['2023-11-15', '2023-11-17 00:00:00'],
            ['2023-11-15 00:00:00', '2023-11-14 00:00:00'],
            ['2023-11-15 00:00:00', '2023-11-15 00:00:00'],
            ['2023-11-15 00:00:00', null],
            [null, '2023-11-15 00:00:00'],
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->accountService = $this->app->make(AccountService::class);
        $this->merchantService = $this->app->make(MerchantService::class);
        $this->paymentService = $this->app->make(PaymentService::class);
        $this->userService = $this->app->make(UserService::class);
    }

    public function test_generate_payments_report(): void
    {
        // given
        $merchantId = $this->thereIsMerchant();
        $userId = $this->thereIsUser($merchantId);

        $payment = new Payment();
        $payment->userId = $userId;
        $payment->paymentId = Uuid::uuid4();
        $payment->merchantId = $merchantId;
        $payment->amount = 20.0;
        $payment->createdAt = '2023-11-01 00:00:00';
        $payment->save();

        $payment = new Payment();
        $payment->userId = $userId;
        $payment->paymentId = Uuid::uuid4();
        $payment->merchantId = $merchantId;
        $payment->amount = 3.5;
        $payment->createdAt = '2023-11-15 00:00:00';
        $payment->save();

        $payment = new Payment();
        $payment->userId = $userId;
        $payment->paymentId = Uuid::uuid4();
        $payment->merchantId = $merchantId;
        $payment->amount = 1.23;
        $payment->createdAt = '2023-11-16 12:34:56';
        $payment->save();

        $payment = new Payment();
        $payment->userId = $userId;
        $payment->paymentId = Uuid::uuid4();
        $payment->merchantId = $merchantId;
        $payment->amount = 14.7;
        $payment->createdAt = '2023-11-17 23:59:58';
        $payment->save();

        $payment = new Payment();
        $payment->userId = $userId;
        $payment->paymentId = Uuid::uuid4();
        $payment->merchantId = $merchantId;
        $payment->amount = 420.69;
        $payment->createdAt = '2023-11-30 00:00:00';
        $payment->save();

        $expectedAmount = 19.43;

        // when
        $requestData = [
            'merchantId' => $merchantId,
            'from'       => '2023-11-15 00:00:00',
            'to'         => '2023-11-17 23:59:59',
        ];

        $response = $this->json('get', 'api/report/payments', $requestData)
            ->assertStatus(Response::HTTP_OK)
            ->json();

        // then
        self::assertEquals($expectedAmount, $response['total'], 'Payment report should only contain data from the selected date period');
    }

    #[DataProvider('invalidDateProvider')]
    public function test_generate_payments_report_invalid_date(?string $from, ?string $to): void
    {
        $requestData = [
            'merchantId' => Uuid::uuid4(),
            'from'       => $from,
            'to'         => $to,
        ];

        $response = $this->json('get', 'api/report/payments', $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->json();
    }

    private function thereIsMerchant(): string
    {
        $merchant = $this->merchantService->addMerchant('DNA');

        return $merchant->getMerchantId();
    }

    /**
     * @throws Exception
     */
    private function thereIsUser(string $merchantId): string
    {
        $user = $this->userService->addUser(
            'Jan Kowalski',
            'jan.kowalski@digitalnewagency.com',
            $merchantId,
        );

        return $user->getUserId();
    }
}
