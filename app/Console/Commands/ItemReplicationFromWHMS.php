<?php

namespace App\Console\Commands;

use App\Http\Controllers\RequisitionItemController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ItemReplicationFromWHMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'replication:item';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Item replication from WHMS';

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
    public function handle(RequisitionItemController $requisitionItemController)
    {
        $requisitionItemController->syncItem();
    }


}
