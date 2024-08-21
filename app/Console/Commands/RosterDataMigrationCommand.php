<?php

namespace App\Console\Commands;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Roster;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RosterDataMigrationCommand extends Command
{

    protected $oldTable = 'roasters';

    protected $newTable = 'rosters';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:data-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migration data old roaster to new roster';

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
        if( !Schema::hasTable($this->oldTable) ) return 0;

        if(!DB::table($this->oldTable)->first()) return 0;

        try {
            $items = DB::table($this->oldTable)->get();

            foreach ($items as $item) {
                if(!User::find($item->user_id)) {
                    Log::info("#    This user not exist our application (user_id={$item->user_id})  #");
                    continue;
                }
                Roster::insert($this->getInsertData($item));
            }
        } catch (Exception $ex) {
            Log::error($ex);
            return 0;
        }
        return 1;
    }

    protected function getInsertData($item)
    {
        $data = [];
        $start = Carbon::parse($item->active_from);
        $end = Carbon::parse($item->end_date);
        $weekly_holidays = json_decode($item->weekly_holidays);
        dump('id='. $item->id);

        while ($start->lte($end)) {
            $data[] = [
                'id' => Str::uuid(),
                'type' => 1,  // employee
                'company_id' => 1, // right now company id is statically assigned
                'department_id' => $item->department_id,
                'user_id' =>$item->user_id,
                'work_slot_id' => $item->work_slot_id,
                'office_division_id' => $item->office_division_id,
                'is_weekly_holiday' => in_array( strtolower($start->format('D')), $weekly_holidays ) ? 1 : 0,
                'active_date' => $start->toDateString(),
                'is_locked' => $item->is_locked,
                'status' => $item->approval_status,
                'remarks' => $item->remarks,
                'approved_by' => $item->approved_by,
                'created_by' => $item->created_by,
                'updated_by' => $item->updated_by,
                'approved_date' => $item->approved_date,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
            $start->addDay();
        }

        return $data;
    }

}
