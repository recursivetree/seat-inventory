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
        if($alliance_tracking != null && $alliance_tracking->automate_corporations){

            //check if corporations is already tracked
            if(!TrackedCorporation::where("corporation_id",$alliance_member->corporation_id)->exists()){
                $db_entry = new TrackedCorporation();
                $db_entry->corporation_id = $alliance_member->corporation_id;
                $db_entry->save();
            }
        }
    }

    public function deleted($alliance_member){
        //delete corporations that aren't tracked anymore

        //find parent alliance
        $alliance_tracking = TrackedAlliance::where("alliance_id",$alliance_member->alliance_id)->first();

        //check if alliance tracks members
        if($alliance_tracking != null && $alliance_tracking->automate_corporations){

            //alliance is tracking members, so delete the tracking
            $tracking = TrackedCorporation::where("corporation_id",$alliance_member->corporation_id)->first();
            if($tracking == null) return;
            TrackedCorporation::destroy($tracking->id);
        }
    }
}