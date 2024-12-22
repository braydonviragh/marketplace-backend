<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;
use App\Http\Requests\PaymentRequest;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(PaymentRequest $request)
    {
        $payments = $this->paymentService->getPayments(
            filters: $request->validated(),
            perPage: $request->per_page ?? 15
        );

        return $this->collectionResponse(
            new PaymentCollection($payments),
            'Payments retrieved successfully'
        );
    }

    public function show(int $id)
    {
        $payment = $this->paymentService->findPayment($id);
        
        return $this->resourceResponse(
            new PaymentResource($payment->load(['payer', 'payee', 'rental'])),
            'Payment retrieved successfully'
        );
    }
} 