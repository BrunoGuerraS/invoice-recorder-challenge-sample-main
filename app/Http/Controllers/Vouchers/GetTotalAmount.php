<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Controllers\Controller;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetTotalAmount extends Controller
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }
    public function __invoke(Request $request): Response
    {
        return response([
            'totalAmount' => "Total amount of vouchers is: totalAmount",
        ]);
    }
}
