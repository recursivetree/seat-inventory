<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Seat\Services\Models\Schedule;

class ScheduleNotifications extends Migration
{
    public function up()
    {
        $schedule = new Schedule();
        $schedule->command = "inventory:notifications";
        $schedule->expression = "0 0 * * *";
        $schedule->allow_overlap = false;
        $schedule->allow_maintenance = false;
        $schedule->save();
    }

    public function down()
    {
        $schedules = Schedule::where("command","inventory:notifications")
            ->where("expression","0 0 * * *")
            ->get();

        foreach ($schedules as $schedule){
            $schedule->delete();
        }
    }
}

