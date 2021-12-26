<?php

namespace RecursiveTree\Seat\TerminusInventory\Http\Controllers;

use Exception;
use RecursiveTree\Seat\TerminusInventory\Models\Stock;
use RecursiveTree\Seat\TerminusInventory\Models\StockItem;
use RecursiveTree\Seat\TerminusInventory\Models\TrackedCorporation;
use RecursiveTree\Seat\TerminusInventory\Helpers\ItemHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\Parser;

use Seat\Web\Http\Controllers\Controller;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Assets\CharacterAsset;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $tracked_corporations = TrackedCorporation::all();

        return view("terminusinv::tracking", compact('tracked_corporations'));
    }

    public function addTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No corporation specified!", 'error');
        }
        if(!CorporationInfo::where("corporation_id",$id)->exists()){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"Corporation not found!", 'error');
        }
        $db_entry = new TrackedCorporation();
        $db_entry->corporation_id = $id;
        $db_entry->save();

        return $this->redirectWithStatus($request,'terminusinv.tracking',"Added corporation!", 'success');
    }

    public function deleteTrackingCorporation(Request $request){
        $id = $request->id;
        if($id==null){
            return $this->redirectWithStatus($request,'terminusinv.tracking',"No corporation provided!", 'error');
        }
        TrackedCorporation::destroy($id);
        return $this->redirectWithStatus($request,'terminusinv.tracking',"Sucessfully removed inventory tracking.", 'success');
    }

    public function stockLocationSuggestions(Request $request){
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

        dd($corporations);

        $suggestions = [];
        foreach ($corporations as $corporation){
            $suggestions[] = [
                "text" => "[$corporation->ticker] $corporation->name",
                "value" => $corporation->corporation_id,
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

    public function addStockPost(Request $request){
        $fit_plugin_id = $request->fit_plugin_id;
        $fit_text = $request->fit_text;
        $multibuy_text = $request->multibuy_text;
        $amount = $request->amount;
        $location = $request->location_id;
        $name = $request->name;

        //check if always required data is there
        if($location==null || $amount==null){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"Not all required data is provided!", 'error');
        }

        //check if the amount is in a valid range
        if($amount<1){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"The minimum stock is 1!", 'error');
        }

        //check if the location is in a valid format
        $location_regexp = [];
        if (!preg_match("/^(?:station\|(?<station_id>\d+))|(?:structure\|(?<structure_id>\d+))$/",$location, $location_regexp)){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"Invalid location!", 'error');
        }

        //new stock entry to fill
        $stock = new Stock();

        //add location
        if(strlen($location_regexp["station_id"])>1){
            $stock->station_id = $location_regexp["station_id"];
        } else if(strlen($location_regexp["structure_id"])>1){
            $stock->structure_id = $location_regexp["structure_id"];
        }

        //items required for the stock
        $required_items = [];

        //check if multibuy data was submitted
        if($multibuy_text!=null){
            $type_ids = Parser::parseMultiBuy($multibuy_text);
            $required_items = array_merge($required_items, $type_ids);
        }

        //check if a eft fit was submitted
        if($fit_text!=null){
            try {
                $fit = Parser::parseFit($fit_text);
            } catch (Exception $e){
                $m = $e->getMessage();
                return $this->redirectWithStatus($request,'terminusinv.stocks',"Could not parse fit: $m", 'error');
            }
            $required_items = array_merge($required_items, $fit["items"]);
            $name = $fit["name"];
        }

        //fill data
        $stock->amount = $amount;
        $stock->check_contracts = true;
        $stock->check_corporation_hangars = true;

        //if there is a link to the fitting plugin, save it
        if($fit_plugin_id!=null){
            $stock->fitting_plugin_fitting_id = $fit_plugin_id;
        }

        //make sure we always have a name
        if($name!=null){
            $stock->name = $name;
        } else {
            $stock->name = "unnamed stock";
        }

        $required_items = ItemHelper::simplifyItemList($required_items);

        DB::transaction(function () use ($required_items, $stock) {
            $stock->save();

            $id=$stock->id;

            foreach ($required_items as $item){
                $item->stock_id = $id;
                $item->save();
            }
        });

        return $this->redirectWithStatus($request,'terminusinv.stocks',"Added stock definition!", 'success');
    }

    public function stocks(Request $request){
        $fittings = Stock::all();

        $has_fitting_plugin = class_exists("Denngarr\Seat\Fitting\Models\Fitting");

        return view("terminusinv::stocks",compact("fittings", "has_fitting_plugin"));
    }

    public function editStock(Request $request,$id){
        $stock = Stock::find($id);

        if($stock==null){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"Could not find stock definition!", 'error');
        }

        $multibuy = ItemHelper::itemListToMultiBuy($stock->items);

        return view("terminusinv::editStock", compact("stock","multibuy"));
    }

    public function deleteStockPost(Request $request,$id){
        if($id!=null) {
            Stock::destroy($id);
        }

        return $this->redirectWithStatus($request,'terminusinv.stocks',"Deleted stock definition!", 'success');
    }

    public function about(){
        return view("terminusinv::about");
    }
}