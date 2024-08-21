<?php

namespace Database\Seeders;

use App\Models\ActionReason;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActionReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $timestamp = Carbon::now();

        ActionReason::insert([
            [
                "parent_id" => 0,
                "name"      => ActionReason::TYPE_JOIN,
                "reason"    => null,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 1,
                "name"      => null,
                "reason"    => ActionReason::TYPE_JOIN,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 0,
                "name"      => ActionReason::TYPE_RESIGN,
                "reason"    => null,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 0,
                "name"      => ActionReason::TYPE_SUSPEND,
                "reason"    => null,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 0,
                "name"      => ActionReason::TYPE_TERMINATE,
                "reason"    => null,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 0,
                "name"      => ActionReason::TYPE_REJOIN,
                "reason"    => null,
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
            [
                "parent_id" => 1,
                "name"      => null,
                "reason"    => "Joined again",
                "created_at"=> $timestamp,
                "updated_at"=> $timestamp
            ],
        ]);
    }
}
