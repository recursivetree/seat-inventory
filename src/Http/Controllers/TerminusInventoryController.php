<?php

namespace RecursiveTree\Seat\TerminusInventory\Http\Controllers;

use RecursiveTree\Seat\TerminusInventory\Models\FittingStock;
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

    public function fittingStockLocationSuggestions(Request $request){
        $query = $request->q;

        $locations = TrackedLocations::all();

        $suggestions = [];
        foreach ($locations as $location){
            if($location->is_structure){
                $stastruct = $location->structure;
            } else if ($location->is_station){
                $stastruct = $location->station;
            } else {
                continue;
            }

            if($query!=null && strpos($stastruct->name,$query)===false){
                continue;
            }

            $suggestions[] = [
                "text" => "$stastruct->name",
                "value" => $location->id,
            ];
        }

        return response()->json($suggestions);
    }

    public function fittingPluginFittingsSuggestions(Request $request){
        if(!class_exists("Denngarr\Seat\Fitting\Models\Fitting")){
            return response()->json([],400);
        }
        $class = "Denngarr\Seat\Fitting\Models\Fitting";

        $query = $request->q;
        if($query==null){
            $fittings = $class::all();
        } else {
            $fittings = $class::where("fitname","like","%$query%")::get();
        }

        $suggestions = [];
        foreach ($fittings as $fit){

            $suggestions[] = [
                "text" => "[$fit->shiptype] $fit->fitname",
                "value" => $fit->id,
            ];
        }

        return response()->json($suggestions);
    }

    public function addFittingPos(Request $request){
        $fit_plugin_id = $request->fit_plugin_id;
        $fit_text = $request->fit_text;
        $amount = $request->amount;
        $location = $request->location_id;

        if($location==null || $amount==null){
            return $this->redirectWithStatus($request,'terminusinv.fittings',"Not all required data is provided!", 'error');
        }

        if($amount<1){
            return $this->redirectWithStatus($request,'terminusinv.fittings',"The minimum stock is 1!", 'error');
        }

        $fitting_stock = new FittingStock();

        $fitting_stock->location_id = $location;
        $fitting_stock->amount = $amount;
        $fitting_stock->ship_type_id = 587;
        $fitting_stock->name = "dummy";
        if($fit_plugin_id!=null){
            $fitting_stock->fitting_plugin_fitting_id = $fit_plugin_id;
        }

        $fitting_stock->save();

        return $this->redirectWithStatus($request,'terminusinv.fittings',"Added fitting stock definition!", 'success');
    }

    public function fittings(Request $request){
        $fittings = FittingStock::all();

        $has_fitting_plugin = class_exists("Denngarr\Seat\Fitting\Models\Fitting");

        return view("terminusinv::fittings",compact("fittings", "has_fitting_plugin"));
    }

    public function about(){
        return view("terminusinv::about");
    }
}