<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;

class AllianceMemberObserver
{
    public function saved($alliance_member){
        //there might be multiple instances of the same alliance in different workspaces
        $alliance_trackings = TrackedAlliance::where("alliance_id",$alliance_member->alliance_id)->get();

        //update alliance in each workspace
        foreach ($alliance_trackings as $alliance_tracking) {
            //check if alliance tracks members
            if ($alliance_tracking->manage_members) {

                //check if corporations is already tracked
                $db_entry = TrackedCorporation::first($alliance_member->corporation_id);

                // if not, create a tracking entry
                if ($db_entry === null) {
                    $db_entry = new TrackedCorporation();
                    $db_entry->corporation_id = $alliance_member->corporation_id;
                }

                //fill data and save
                $db_entry->managed_by = $alliance_member->alliance_id;
                $db_entry->workspace_id = $alliance_tracking->workspace_id;
                $db_entry->save();
            }
        }
    }

    public function deleted($alliance_member){
        //delete corporations that aren't tracked anymore

        //find parent alliances. there might be multiple entries because of workspaces
        $alliance_trackings = TrackedAlliance::where("alliance_id",$alliance_member->alliance_id)->get();

        //go over each alliance entry
        foreach ($alliance_trackings as $alliance_tracking){
            //check if the alliance is automated
            if($alliance_tracking->manage_members){
                //remove corporation
                TrackedCorporation::where("corporation_id",$alliance_member->corporation_id)
                    ->where("workspace_id",$alliance_tracking->workspace_id)
                    ->where("managed_by",$alliance_member->alliance_id)
                    ->first()
                    ->delete();
            }
        }
    }
}