<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Controllers\Controller;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetTotalAmount extends Controller
{
    public function __construct(private readonly VoucherService $voucherService){}
    public function __invoke(Request $request): Response
    {
        try {
            $totalAmount = $this->voucherService->getTotalAmount($request->id);
            return response(
                $totalAmount
            );
        } catch (\Exception $e) {
            return response([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
