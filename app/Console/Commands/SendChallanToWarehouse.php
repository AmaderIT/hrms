<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\InternalTransfer;
use App\Http\Controllers\InternalTransferController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendChallanToWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-challan-to-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Challan send to WHMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(InternalTransferController $internalTransferController)
    {
        $internalTransferController->sendChallanToWarehouse();
        $internalTransferController->sendReturnChallanToWarehouse();
        $internalTransferController->sendRejectChallanToWarehouse();
    }
}
