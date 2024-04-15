<?php

use App\Http\Controllers\Vouchers\DeleteVoucher;
use App\Http\Controllers\Vouchers\GetTotalAmount;
use App\Http\Controllers\Vouchers\GetVouchersHandler;
use App\Http\Controllers\Vouchers\StoreVouchersHandler;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->group(
    function () {
        Route::get('/', GetVouchersHandler::class);

        Route::post('/', StoreVouchersHandler::class);

        Route::get('/amount/{id}', GetTotalAmount::class);

        Route::delete('/{id}', DeleteVoucher::class);

    }
);
