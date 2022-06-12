<?php

namespace RecursiveTree\Seat\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Helpers\Parser;
use RecursiveTree\Seat\Inventory\Jobs\UpdateStockLevels;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use Seat\Eveapi\Models\Sde\InvType;
use Seat\Web\Http\Controllers\Controller;

class LegacyController extends Controller
{
    private function redirectWithStatus($request,$redirect,$message,$type){
        $request->session()->flash('message', [
            'message' => $message,
            'type' => $type
        ]);
        return redirect()->route($redirect);
    }

    public function locationSuggestions(Request $request){
        $query = $request->q;
        $suggestions = [];

        if($query==null){
            $locations = Location::all();
        } else {
            $locations = Location::where("name","like","%$query%")->get();
        }

        foreach ($locations as $location){
            $suggestions[] = [
                "text" => $location->name,
                "value" => $location->id,
            ];
        }

        return response()->json($suggestions);
    }

    public function itemTypeSuggestions(Request $request){
        $query = $request->q;
        if($query==null){
            $types = InvType::where("marketGroupID", "!=", null)->get();
        } else {
            $types = InvType::where("marketGroupID", "!=", null)->where("typeName","like","%$query%")->limit(100)->get();
        }

        $suggestions = [];
        foreach ($types as $type){

            $suggestions[] = [
                "text" => "$type->typeName",
                "value" => $type->typeID,
            ];
        }

        return response()->json($suggestions);
    }

    public function itemBrowser(Request $request){
        $location_id = $request->location_id;
        $location_id_text = $request->location_id_text;
        $filter_item_type = $request->item_id;
        $filter_item_type_text = $request->item_id_text;
        $check_corporation_hangars = $request->checkbox_corporation_hangar!=null;
        $check_contracts = $request->checkbox_contracts!=null;
        $check_fitted_ships = $request->checkbox_fitted_ships!=null;
        $check_in_transport = $request->checkbox_in_transport!=null;

        $allowed_types = [];

        $has_no_filter = ($location_id==null)
            && ($filter_item_type == null)
            && ($check_corporation_hangars == false)
            && ($check_contracts == false)
            && ($check_fitted_ships == false)
            && ($check_in_transport == false);

        $show_results = !(($location_id==null) && ($filter_item_type == null));

        $check_contracts = $check_contracts || $has_no_filter;
        $check_corporation_hangars = $check_corporation_hangars || $has_no_filter;
        $check_fitted_ships = $check_fitted_ships || $has_no_filter;
        $check_in_transport = $check_in_transport || $has_no_filter;

        if($check_corporation_hangars){
            $allowed_types[] = "corporation_hangar";
        }
        if($check_contracts){
            $allowed_types[] = "contract";
        }
        if($check_fitted_ships){
            $allowed_types[] = "fitted_ship";
        }
        if($check_in_transport){
            $allowed_types[] = "in_transport";
        }

        if($location_id != null && Location::find($location_id) == null){
            return $this->redirectWithStatus($request,'inventory.itemBrowser',"Location not found!", 'error');
        }

        if($show_results) {
            $query = InventorySource::whereIn("source_type", $allowed_types);

            //filter location
            if ($location_id != null) {
                $query = $query->where("location_id", $location_id);
            }

            $inventory_sources = $query->orderBy("location_id", "ASC")->get();
        } else {
            $inventory_sources = collect();
        }

        //item filter
        if($filter_item_type!=null) {
            $inventory_sources = $inventory_sources->filter(function ($source) use ($filter_item_type) {
                return $source->items->where("type_id", $filter_item_type)->count() > 0;
            });
        }

        return view("inventory::itembrowser", compact(
            "inventory_sources",
            "filter_item_type",
            "check_contracts",
            "check_corporation_hangars",
            "check_fitted_ships",
            "check_in_transport",
            "location_id",
            "location_id_text",
            "filter_item_type",
            "filter_item_type_text",
            "show_results"
        ));
    }
}