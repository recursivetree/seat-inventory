<?php

namespace RecursiveTree\Seat\TerminusInventory\Http\Controllers;

use Exception;
use RecursiveTree\Seat\TerminusInventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\LocationHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\StockHelper;
use RecursiveTree\Seat\TerminusInventory\Models\InventorySource;
use RecursiveTree\Seat\TerminusInventory\Models\Location;
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
use Seat\Eveapi\Models\Sde\InvType;

class TerminusInventoryController extends Controller
{
    private function redirectWithStatus($request,$redirect,$message,$type){
        $request->session()->flash('message', [
            'message' => $message,
            'type' => $type
        ]);
        return redirect()->route($redirect);
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

    public function fittingPluginFittingsSuggestions(Request $request){
        if(!FittingPluginHelper::pluginIsAvailable()){
            return response()->json([],400);
        }

        $query = $request->q;
        if($query==null){
            $fittings = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::all();
        } else {
            $fittings = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::where("fitname","like","%$query%")->get();
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

    public function itemTypeSuggestions(Request $request){
        $query = $request->q;
        if($query==null){
            $types = InvType::where("marketGroupID", "!=", null)->get();
        } else {
            $types = InvType::where("marketGroupID", "!=", null)->where("typeName","like","$query%")->limit(100)->get();
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

    public function stockSuggestions(Request $request){
        $query = $request->q;
        if($query==null){
            $stocks = Stock::all();
        } else {
            $stocks = Stock::where("name", "like", "$query%")->limit(100)->get();
        }

        $suggestions = [];
        foreach ($stocks as $stock){

            $suggestions[] = [
                "text" => "$stock->name",
                "value" => $stock->id,
            ];
        }

        return response()->json($suggestions);
    }

    public function addStockPost(Request $request){
        $fit_plugin_id = $request->fit_plugin_id;
        $fit_text = $request->fit_text;
        $multibuy_text = $request->multibuy_text;
        $amount = $request->amount;
        $location_id = $request->location_id;
        $name = $request->name;

        //check if always required data is there
        if($location_id==null || $amount==null){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"Not all required data is provided!", 'error');
        }

        //check if the amount is in a valid range
        if($amount<1){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"The minimum amount is 1!", 'error');
        }

        //check location
        $location = Location::find($location_id);
        if($location == null){
            return $this->redirectWithStatus($request,'terminusinv.stocks',"Location not found!", 'error');
        }

        //items required for the stock
        $required_items = [];

        //check if multi-buy data was submitted
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

        if($fit_plugin_id != null && FittingPluginHelper::pluginIsAvailable()){
            $model = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::find($fit_plugin_id);
            if($model == null){
                return $this->redirectWithStatus($request,'terminusinv.stocks',"The fit could not be retrieved from the fitting plugin!", 'error');
            }
            try {
                $fit = Parser::parseFit($model->eftfitting);
            } catch (Exception $e){
                $m = $e->getMessage();
                return $this->redirectWithStatus($request,'terminusinv.stocks',"Could not parse fit: $m", 'error');
            }
            $required_items = array_merge($required_items, $fit["items"]);
            $name = $fit["name"];
        }

        //new stock entry to fill
        $stock = new Stock();

        //fill data
        $stock->amount = $amount;
        $stock->check_contracts = true;
        $stock->check_corporation_hangars = true;
        $stock->location_id = $location->id;

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

            foreach ($required_items as $item_helper){
                $item = $item_helper->asStockItem();
                $item->stock_id = $id;
                $item->save();
            }
        });

        return $this->redirectWithStatus($request,'terminusinv.stocks',"Added stock definition!", 'success');
    }

    public function stocks(Request $request){
        $fittings = Stock::all();
        $has_fitting_plugin = FittingPluginHelper::pluginIsAvailable();

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

        $items = StockItem::where("stock_id",$id)->get();
        foreach ($items as $item){
            $item->destroy($item->id);
        }

        return $this->redirectWithStatus($request,'terminusinv.stocks',"Deleted stock definition!", 'success');
    }

    public function itemBrowser(Request $request){
        $location_id = $request->location_id;
        $filter_item_type = $request->item_id;
        $allowed_types = [];

        if($location_id != null && Location::find($location_id) == null){
            return $this->redirectWithStatus($request,'terminusinv.itemBrowser',"Location not found!", 'error');
        }

        if($request->checkbox_corporation_hangar!=null || $request->filter == null){
            $allowed_types[] = "corporation_hangar";
        }
        if($request->checkbox_contracts!=null || $request->filter == null){
            $allowed_types[] = "contract";
        }

        $query = InventorySource::whereIn("source_type", $allowed_types);

        //filter location
        if($location_id!=null){
            $query = $query->where("location_id",$location_id);
        }

        $inventory_sources = $query->orderBy("location_id","ASC")->get();

        //item filter
        if($filter_item_type!=null) {
            $inventory_sources = $inventory_sources->filter(function ($source) use ($filter_item_type) {
                return $source->items->where("type_id", $filter_item_type)->count() > 0;
            });
        }

        return view("terminusinv::itembrowser", compact("inventory_sources","request", "filter_item_type"));
    }

    public function stockAvailability(Request $request){
        $location = null;
        $stock = null;

        if($request->location_id==null){
            $location = Location::find($request->location_id);
        }

        if($request->stock_id){
            $stock = Stock::find($request->stock_id);
            if ($stock != null){
                $location = $stock->location;
            }
        }

        if($location == null){
            return view("terminusinv::availability", compact("request"));
        }

        StockHelper::computeStockLevels($location, $stock);

        return view("terminusinv::availability", compact("request",));
    }

    public function about(){
        return view("terminusinv::about");
    }
}