<?php

namespace RecursiveTree\Seat\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Helpers\LocationHelper;
use RecursiveTree\Seat\Inventory\Helpers\StockHelper;
use RecursiveTree\Seat\Inventory\Jobs\UpdateInventory;
use RecursiveTree\Seat\Inventory\Models\TrackedAlliance;
use RecursiveTree\Seat\Inventory\Models\TrackedCorporation;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Http\Controllers\Controller;

class TrackingController extends Controller
{
    public function settings(){
        return view("inventory::settings");
    }

    public function listCorporations(){
        $corporations = TrackedCorporation::with(["corporation","alliance"])->get();

        return response()->json($corporations);
    }

    public function addCorporation(Request $request){
        $request->validate([
            "corporation_id"=>"required|integer"
        ]);

        if(!CorporationInfo::where("corporation_id",$request->corporation_id)->exists()){
            return response()->json(["message"=>"corporation doesn't exist"],400);
        }

        if(TrackedCorporation::where("corporation_id",$request->corporation_id)->exists()){
            $corp = TrackedCorporation::find($request->corporation_id);
            //special case: add corporation permanently
            if($corp->managed_by !== null){
                $corp->managed_by = null;
                $corp->save();
                return response()->json();
            } else {
                return response()->json(["message" => "corporation is already tracked"], 400);
            }
        }

        //save it to the db
        $db_entry = new TrackedCorporation();
        $db_entry->corporation_id = $request->corporation_id;
        $db_entry->save();

        //new corporations, new assets -> we need to update
        UpdateInventory::dispatch()->onQueue('default');

        return response()->json();
    }

    public function removeCorporation(Request $request){
        $request->validate([
            "corporation_id"=>"required|integer"
        ]);

        TrackedCorporation::destroy($request->corporation_id);

        //-1 corporation, less assets -> we need to update
        UpdateInventory::dispatch()->onQueue('default');

        return response()->json();
    }

    public function corporationLookup(Request $request){
        $request->validate([
            "term"=>"nullable|string",
            "id"=>"nullable|integer"
        ]);

        $query = CorporationInfo::query();

        if($request->term){
            $query = $query->where("name","like","%$request->term%");
        }

        if($request->id){
            $query = $query->where("id",$request->id);
        }

        $suggestions = $query->get();

        $suggestions = $suggestions
            ->map(function ($corporation){
                return [
                    'id' => $corporation->corporation_id,
                    'text' => "$corporation->name"
                ];
            });

        return response()->json([
            'results'=>$suggestions
        ]);
    }

    public function allianceLookup(Request $request){
        $request->validate([
            "term"=>"nullable|string",
            "id"=>"nullable|integer"
        ]);

        $query = Alliance::query();

        if($request->term){
            $query = $query->where("name","like","%$request->term%");
        }

        if($request->id){
            $query = $query->where("id",$request->id);
        }

        $suggestions = $query->get();

        $suggestions = $suggestions
            ->map(function ($alliance){
                return [
                    'id' => $alliance->alliance_id,
                    'text' => "$alliance->name"
                ];
            });

        return response()->json([
            'results'=>$suggestions
        ]);
    }

    public function addAlliance(Request $request){
        $request->validate([
            "alliance_id"=>"required|integer"
        ]);

        if(!Alliance::where("alliance_id",$request->alliance_id)->exists()){
            return response()->json(["message"=>"alliance doesn't exist"],400);
        }

        if(TrackedAlliance::where("alliance_id",$request->alliance_id)->exists()){
            return response()->json(["message"=>"alliance already trackes"],40);
        }

        $db_entry = new TrackedAlliance();
        $db_entry->alliance_id = $request->alliance_id;
        $db_entry->manage_members = false;
        $db_entry->save();

        return response()->json([]);
    }

    public function removeAlliance(Request $request){
        $request->validate([
            "alliance_id"=>"required|integer"
        ]);

        $alliance = TrackedAlliance::find($request->alliance_id);

        if($alliance === null){
            return response()->json(["message"=>"alliance doesn't exist"],400);
        }

        if($alliance->manange_members){
            $corporations = TrackedCorporation::where("managed_by",$request->alliance_id)->get();
            foreach ($corporations as $corporation){
                $corporation->delete();
            }
        }
        $alliance->delete();
        return response()->json([]);
    }

    public function listAlliances(){
        $corporations = TrackedAlliance::with("alliance")->get();

        return response()->json($corporations);
    }

    public function addAllianceMembers(Request $request){
        $request->validate([
            "alliance_id"=>"required|integer"
        ]);

        $tracking = TrackedAlliance::find($request->alliance_id);

        if($tracking === null){
            return response()->json(["message"=>"alliance isn't tracked"],400);
        }

        $tracking->manage_members = true;
        $tracking->save();

        foreach ($tracking->alliance->members as $member){
            $corp = TrackedCorporation::where("corporation_id",$member->corporation_id)->first();
            //skip manually added corporations
            if($corp!==null && $corp->managed_by === null) continue;
            // update corporation
            if($corp === null){
                $corp = new TrackedCorporation();
                $corp->corporation_id = $member->corporation_id;
            }
            $corp->managed_by = $request->alliance_id;
            $corp->save();
        }
        return response()->json([]);
    }

    public function removeAllianceMembers(Request $request)
    {
        $request->validate([
            "alliance_id" => "required|integer"
        ]);

        $tracking = TrackedAlliance::find($request->alliance_id);

        if ($tracking === null) {
            return response()->json(["message" => "alliance isn't tracked"], 400);
        }

        $tracking->manage_members = false;
        $tracking->save();

        TrackedCorporation::where("managed_by",$request->alliance_id)->delete();

        return response()->json([]);
    }

    //OLD

    public function tracking(){
        $tracked_corporations = TrackedCorporation::all();
        $tracked_alliances = TrackedAlliance::all();
        return view("inventory::tracking", compact('tracked_corporations','tracked_alliances'));
    }

    private function redirectWithStatus($request,$redirect,$message,$type){
        $request->session()->flash('message', [
            'message' => $message,
            'type' => $type
        ]);
        return redirect()->route($redirect);
    }

    public function addTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'inventory.tracking',"No corporation specified!", 'error');
        }
        if(!CorporationInfo::where("corporation_id",$id)->exists()){
            return $this->redirectWithStatus($request,'inventory.tracking',"Corporation not found!", 'error');
        }
        if(TrackedCorporation::where("corporation_id",$id)->exists()){
            return $this->redirectWithStatus($request,'inventory.tracking',"Corporation is already added to the list of tracked corporations!", 'warning');
        }

        $db_entry = new TrackedCorporation();
        $db_entry->corporation_id = $id;
        $db_entry->save();

        //new corporations, new assets
        UpdateInventory::dispatch()->onQueue('default');

        return $this->redirectWithStatus($request,'inventory.tracking',"Added corporation!", 'success');
    }

    public function addTrackingAlliance(Request $request){
        $id = $request->id;
        $corporation_handling = $request->alliance_corporation_handling ?: "manage";

        if($corporation_handling==="manage"){
            $add_members = true;
            $manage_members = true;
        } elseif($corporation_handling==="add") {
            $add_members = true;
            $manage_members = false;
        } elseif($corporation_handling==="no") {
            $add_members = false;
            $manage_members = false;
        } else {
            return $this->redirectWithStatus($request,'inventory.tracking',"No valid option corporation tracking option specified!", 'error');
        }

        if($id==null){
            return $this->redirectWithStatus($request,'inventory.tracking',"No alliance specified!", 'error');
        }

        if(!Alliance::where("alliance_id",$id)->exists()){
            return $this->redirectWithStatus($request,'inventory.tracking',"Alliance not found!", 'error');
        }
        if(TrackedAlliance::where("alliance_id",$id)->exists()){
            return $this->redirectWithStatus($request,'inventory.tracking',"Alliance is already added to the list of tracked alliances!", 'warning');
        }

        $db_entry = new TrackedAlliance();
        $db_entry->alliance_id = $id;
        $db_entry->manage_members = $manage_members;
        $db_entry->save();

        if($add_members){
            //get alliance members
            $alliance = Alliance::find($id);

            foreach ($alliance->members as $member_corporation){
                if(!TrackedCorporation::where("corporation_id",$member_corporation->corporation_id)->exists()){
                    $db_entry = new TrackedCorporation();
                    $db_entry->corporation_id = $member_corporation->corporation_id;
                    if($manage_members) {
                        $db_entry->managed_by = $id;
                    }
                    $db_entry->save();
                }
            }
        }

        //new corporations, new assets
        UpdateInventory::dispatch()->onQueue('default');

        return $this->redirectWithStatus($request,'inventory.tracking',"Added alliance!", 'success');
    }

    public function deleteTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'inventory.tracking',"No corporation provided!", 'error');
        }
        TrackedCorporation::where("corporation_id",$id)->delete();

        //remove assets from old alliance
        UpdateInventory::dispatch()->onQueue('default');

        return $this->redirectWithStatus($request,'inventory.tracking',"Successfully removed inventory tracking.", 'success');
    }

    public function deleteTrackingAlliance(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'inventory.tracking',"No alliance provided!", 'error');
        }
        $model = TrackedAlliance::where("alliance_id",$id)->first();
        TrackedAlliance::where("alliance_id",$id)->delete();

        if($model->manage_members){
            TrackedCorporation::where("managed_by",$id)->delete();
        }

        //remove assets from old alliance
        UpdateInventory::dispatch()->onQueue('default');

        return $this->redirectWithStatus($request,'inventory.tracking',"Successfully removed inventory tracking.", 'success');
    }

    public function trackingCorporationSuggestions(Request $request){
        $query = $request->q;

        if ($query==null){
            $corporations = CorporationInfo::all();
        } else {
            $corporations = CorporationInfo::where("name","like","%$query%")->orWhere("ticker","like","%$query%")->get();
        }

        $suggestions = [];
        foreach ($corporations as $corporation){
            $suggestions[] = [
                "text" => "[$corporation->ticker] $corporation->name",
                "value" => $corporation->corporation_id,
            ];
        }

        return response()->json($suggestions);
    }

    public function trackingAllianceSuggestions(Request $request){
        $query = $request->q;

        if ($query==null){
            $alliances = Alliance::all();
        } else {
            $alliances = Alliance::where("name","like","%$query%")->get();
        }

        $suggestions = [];
        foreach ($alliances as $alliance){
            $suggestions[] = [
                "text" => $alliance->name,
                "value" => $alliance->alliance_id,
            ];
        }

        return response()->json($suggestions);
    }
}