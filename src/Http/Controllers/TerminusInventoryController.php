<?php

namespace RecursiveTree\Seat\TerminusInventory\Http\Controllers;

use RecursiveTree\Seat\TerminusInventory\Models\TrackedLocations;
use RecursiveTree\Seat\TerminusInventory\Models\TrackedCorporations;

use Seat\Web\Http\Controllers\Controller;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Illuminate\Http\Request;

class TerminusInventoryController extends Controller
{
    private function redirectWithStatus($request,$redirect,$message,$type){
        $request->session()->flash('message', [
            'message' => $message,
            'type' => $type
        ]);
        return redirect()->route($redirect);
    }

    public function home(){
        $data = CorporationAsset::all();
        $lst = [];
        foreach ($data as $e){
            $lst[] = $e->type->typeName;
        }
        dd($lst);
        //return view("terminusinv::datasources");
    }

    public function tracking(){
        $tracked_locations = TrackedLocations::all();
        $tracked_corporations = TrackedCorporations::all();

        return view("terminusinv::tracking", compact('tracked_corporations','tracked_locations'));
    }

    public function addTrackingLocation(Request $request){
        $request_string = $request->location;

        if($request_string==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No location specified!", 'error');
        } else {
            $parts = explode("|",$request_string);
            if (count($parts)!=2){
                return $this->redirectWithStatus($request,'terminusinv.tracking',"Invalid location!", 'error');
            }

            if ($parts[0]=="station"){
                $station = UniverseStation::where("station_id",$parts[1])->first();
                if($station==null){
                    return $this->redirectWithStatus($request,'terminusinv.tracking',"Could not find location!", 'error');
                }
                $tracked_location = new TrackedLocations();
                $tracked_location->location_id = $parts[1];
                $tracked_location->is_station = true;
                $tracked_location->is_structure = false;
                $tracked_location->save();
            } elseif ($parts[0]=="structure") {
                $structure = UniverseStructure::where("structure_id",$parts[1])->first();
                if($structure==null){
                    return $this->redirectWithStatus($request,'terminusinv.tracking',"Could not find location!", 'error');
                }
                $tracked_location = new TrackedLocations();
                $tracked_location->location_id = $parts[1];
                $tracked_location->is_station = false;
                $tracked_location->is_structure = true;
                $tracked_location->save();
            } else {
                return $this->redirectWithStatus($request,'terminusinv.tracking',"Invalid location!", 'error');
            }
        }

        return $this->redirectWithStatus($request,'terminusinv.tracking',"Added location!", 'success');
    }

    public function addTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No corporation specified!", 'error');
        }
        if(!CorporationInfo::where("corporation_id",$id)->exists()){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"Corporation not found!", 'error');
        }
        $db_entry = new TrackedCorporations();
        $db_entry->corporation_id = $id;
        $db_entry->save();

        return $this->redirectWithStatus($request,'terminusinv.tracking',"Added corporation!", 'success');
    }

    public function deleteTrackingLocation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No location provided!", 'error');
        }
        TrackedLocations::destroy($id);
        return $this->redirectWithStatus($request,'terminusinv.tracking',"Hey", 'success');
    }

    public function deleteTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No corporation provided!", 'error');
        }
        TrackedCorporations::destroy($id);
        return $this->redirectWithStatus($request,'terminusinv.tracking',"Sucessfully removed inventory tracking.", 'success');
    }

    public function trackingLocationSuggestions(Request $request){
        $query = $request->q;
        $suggestions = [];

        if($query==null){
            $structures = UniverseStructure::all();
            $stations = UniverseStation::all();
        } else {
            $structures = UniverseStructure::where("name","like","%$query%")->get();
            $stations = UniverseStation::where("name","like","%$query%")->get();
        }

        foreach ($structures as $structure){
            $suggestions[] = [
                "text" => $structure->name,
                "value" => "structure|$structure->structure_id",
            ];
        }

        foreach ($stations as $station){
            $suggestions[] = [
                "text" => $station->name,
                "value" => "station|$station->station_id",
            ];
        }

        return response()->json($suggestions);
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

    public function about(){
        return view("terminusinv::about");
    }
}