<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VoucherRegisterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $xmlContents;
    protected $user;
     /**
     * Create a new job instance.
     *
     * @param array $xmlContents
     * @param User $user
     */
    public function __construct(array $xmlContents, User $user)
    {
        $this->xmlContents = $xmlContents;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(VoucherService $voucherService): void
    {
        Log::info("VoucherRegisterJob executed!");
        $voucherService->storeVouchersFromXmlContents($this->xmlContents, $this->user);
    }
}
