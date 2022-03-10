<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;

class AllianceMemberObserver
{
    public function saved($alliance_member){
        $alliance_tracking = TrackedAlliance::where("alliance_id",$alliance_member->alliance_id)->first();

        //check if alliance tracks members
        if($alliance_tracking != null && $alliance_tracking->manage_members){

            //check if corporations is already tracked
            if(!TrackedCorporation::where("corporation_id",$alliance_member->corporation_id)->exists()){
                $db_entry = new TrackedCorporation();
                $db_entry->corporation_id = $alliance_member->corporation_id;
                $db_entry->managed_by = $alliance_member->alliance_id;
                $db_entry->save();
            }
        }
    }

    public function deleted($alliance_member){
        //delete corporations that aren't tracked anymore

        //find parent alliance
        $alliance_tracking = TrackedAlliance::where("alliance_id",$alliance_member->alliance_id)->first();

        //check if alliance tracks members
        if($alliance_tracking != null && $alliance_tracking->manage_members){

            //alliance is tracking members, so delete the tracking
            TrackedCorporation::where("corporation_id",$alliance_member->corporation_id)->delete();
        }
    }
}