<?php

namespace App\Console\Commands;

use App\Helpers\Common;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationEmploymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:migration-employment-status {fingerprint_no?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        Log::info("###Migration Employment Status Start###");
        $fingerprintNo = $this->argument('fingerprint_no');
        try {
            $expMsg = "";
            DB::beginTransaction();
            if(!empty($fingerprintNo)){
                $allEmployeeLists = User::select("id", "name", "email", "fingerprint_no")->where(['fingerprint_no'=>$fingerprintNo])->orderBy("id")->get();
            }else{
                $allEmployeeLists = User::select("id", "name", "email", "fingerprint_no")->orderBy("id")->get();
            }
            $countable = 0;
            foreach ($allEmployeeLists as $employee) {
                $exists = Promotion::select("id","employment_type","promoted_date","type")->where(["user_id"=>$employee['id']])->orderBy("promoted_date","ASC")->first();
                if(empty($exists->employment_type) && !empty($exists->promoted_date) && in_array($exists->type, array_keys(Promotion::employmentType()))){
                    //Log::info($employee['id']);
                    $res = Common::modifyPromotionEmploymentTypeEmployeeWise($employee['id']);
                    if(empty($res['errorMsg'])){
                        ++$countable;
                    }
                }
                if(!empty($res['errorMsg'])){
                    throw new \Exception($res['errorMsg']);
                }
            }
            DB::commit();
        }catch (\Exception $ex){
            DB::rollBack();
            $expMsg = $ex->getMessage();
        }
        if(!empty($expMsg)){
            Log::info("Exception Msg : ".$expMsg);
            $this->info("Problem Occurred!!");
        }else{
            Log::info("Total updated row ".$countable);
            Log::info("###Success###");
            $this->info("Success[ Total updated row : ".$countable." ]");
        }
        Log::info("###Migration Employment Status End###");

    }
}
