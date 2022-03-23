<?php

namespace RecursiveTree\Seat\Inventory\Http\Controllers;

use Exception;
use RecursiveTree\Seat\Inventory\Helpers\DoctrineCategorySyncHelper;
use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Helpers\LocationHelper;
use RecursiveTree\Seat\Inventory\Helpers\StockHelper;
use RecursiveTree\Seat\Inventory\Jobs\UpdateStockLevels;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;
use RecursiveTree\Seat\Inventory\Models\StockItem;
use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Helpers\Parser;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Sde\InvType;

class InventoryController extends Controller
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

    public function fittingPluginFittingsSuggestions(Request $request){
        if(!FittingPluginHelper::pluginIsAvailable()){
            return response()->json([],400);
        }

        $query = $request->q;
        if($query==null){
            $fittings = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::all();
        } else {
            $fittings = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::where("fitname","like","%$query%")->orWhere("shiptype","like","%$query%")->get();
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

    public function stockSuggestions(Request $request){
        $query = $request->q;
        if($query==null){
            $stocks = Stock::all();
        } else {
            $stocks = Stock::where("name", "like", "%$query%")->limit(100)->get();
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

    public function saveStockPost(Request $request){
        $fit_plugin_id = $request->fit_plugin_id;
        $fit_text = $request->fit_text;
        $multibuy_text = $request->multibuy_text;
        $amount = $request->amount;
        $location_id = $request->location_id;
        $name = $request->name;
        $check_contracts = $request->check_contracts != null;
        $check_corporation_hangars = $request->check_corporation_hangars != null;
        $priority = $request->priority ?: 0;

        //check if always required data is there
        if($location_id==null || $amount==null){
            return $this->redirectWithStatus($request,'inventory.stocks',"Not all required data is provided!", 'error');
        }

        //check if the amount is in a valid range
        if($amount<1){
            return $this->redirectWithStatus($request,'inventory.stocks',"The minimum amount is 1!", 'error');
        }

        //check location
        $location = Location::find($location_id);
        if($location == null){
            return $this->redirectWithStatus($request,'inventory.stocks',"Location not found!", 'error');
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
                return $this->redirectWithStatus($request,'inventory.stocks',"Could not parse fit: $m", 'error');
            }
            $required_items = array_merge($required_items, $fit["items"]);
            $name = $fit["name"];
        }

        if($fit_plugin_id != null && FittingPluginHelper::pluginIsAvailable()){
            $model = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::find($fit_plugin_id);
            if($model == null){
                return $this->redirectWithStatus($request,'inventory.stocks',"The fit could not be retrieved from the fitting plugin!", 'error');
            }
            try {
                $fit = Parser::parseFit($model->eftfitting);
            } catch (Exception $e){
                $m = $e->getMessage();
                return $this->redirectWithStatus($request,'inventory.stocks',"Could not parse fit: $m", 'error');
            }
            $required_items = array_merge($required_items, $fit["items"]);
            $name = $fit["name"];
        }

        if($request->stock_id == null) {
            //new stock entry to fill
            $stock = new Stock();
        } else {
            $stock = Stock::find($request->stock_id);
            if($stock == null){
                $stock = new Stock();
            }
        }

        //fill data
        $stock->amount = $amount;
        $stock->check_contracts = $check_contracts;
        $stock->check_corporation_hangars = $check_corporation_hangars;
        $stock->location_id = $location->id;
        $stock->priority = $priority;

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
            $stock->items()->delete();

            $stock->save();

            $id=$stock->id;

            foreach ($required_items as $item_helper){
                $item = $item_helper->asStockItem();
                $item->stock_id = $id;
                $item->save();
            }
        });

        //update stock levels for new stock
        UpdateStockLevels::dispatch($location->id)->onQueue('default');

        //if it is in a doctrine, we have to add categories
        DoctrineCategorySyncHelper::syncStock($stock);

        return $this->redirectWithStatus($request,'inventory.stocks',"Added stock definition!", 'success');
    }

    public function editStock($id, Request $request){
        $stock = Stock::find($id);
        if($stock == null){
            return $this->redirectWithStatus($request,'inventory.stocks',"Could not find stock definition!", 'error');
        }

        $multibuy = ItemHelper::itemListToMultiBuy(ItemHelper::itemListFromQuery($stock->items));

        return view("inventory::editStock", compact("stock","multibuy"));
    }

    public function newStock(){
        $has_fitting_plugin = FittingPluginHelper::pluginIsAvailable();
        return view("inventory::newStock", compact("has_fitting_plugin"));
    }

    public function stocks(Request $request){
        $fittings = Stock::all();

        return view("inventory::stocks",compact("fittings" ));
    }

    public function viewStock(Request $request,$id){
        $stock = Stock::find($id);

        if($stock==null){
            return $this->redirectWithStatus($request,'inventory.stocks',"Could not find stock definition!", 'error');
        }

        $items = ItemHelper::itemListFromQuery($stock->items);

        $missing = ItemHelper::missingListFromQuery($stock->items);

        //dd(json_encode($missing));

        $missing_multibuy = ItemHelper::itemListToMultiBuy($missing);

        $multibuy = ItemHelper::itemListToMultiBuy($items);

        return view("inventory::viewStock", compact("stock","multibuy","missing_multibuy","missing"));
    }

    public function deleteStockPost(Request $request,$id){

        $stock = Stock::find($id);

        if($stock !== null) {

            Stock::destroy($id);

            StockItem::where("stock_id", $id)->delete();

            return $this->redirectWithStatus($request, 'inventory.stocks', "Deleted stock definition!", 'success');
        } else {
            return $this->redirectWithStatus($request, 'inventory.stocks', "You are attempting to delete a non-existent stock. Try to refresh your page.", 'error');
        }
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

    public function stockAvailability(Request $request){
        $location_id = $request->location_id ?: null;
        $location_id_text = $request->location_id_text ?: null;

        if($location_id != null) {
            $stocks = Stock::where("location_id", $location_id)->orderBy("priority","DESC")->get();
            $stock_ids = $stocks->pluck("id");

            $missing_items = ItemHelper::missingListFromQuery(StockItem::whereIn("stock_id",$stock_ids)->where("missing_items",">","0")->get());
            $missing_items = ItemHelper::simplifyItemList($missing_items);
            $missing_multibuy = ItemHelper::itemListToMultiBuy($missing_items);

            return view("inventory::availability", compact("stocks", "location_id", "location_id_text", "missing_multibuy", "missing_items"));
        } else {
            return view("inventory::availability", compact( "location_id", "location_id_text"));
        }
    }

    public function about(){
        return view("inventory::about");
    }

    public function getMovingItems(Request $request){
        $sources = InventorySource::where("source_type","in_transport")->get();

        return view("inventory::movingItems",compact("sources"));
    }

    public function addMovingItems(Request $request){
        $location_id = $request->location_id;
        $multibuy = $request->multibuy_text;

        if(!$location_id){
            return $this->redirectWithStatus($request,'inventory.movingItems',"No location specified!", 'error');
        }

        if (!Location::find($location_id)){
            return $this->redirectWithStatus($request,'inventory.movingItems',"Location not found!", 'error');
        }

        if (!$multibuy){
            return $this->redirectWithStatus($request,'inventory.movingItems',"No items found!", 'error');
        }

        $itemList = Parser::parseMultiBuy($multibuy);
        $itemList = ItemHelper::simplifyItemList($itemList);

        if(count($itemList)>0) {
            $source = new InventorySource();
            $source->location_id = $location_id;
            $source->source_name = "Pending Delivery";
            $source->source_type = "in_transport";
            $source->save();

            foreach ($itemList as $item){
                $source_item = $item->asSourceItem();
                $source_item->source_id = $source->id;
                $source_item->save();
            }

            //update stock levels for new stock
            UpdateStockLevels::dispatch($source->location_id)->onQueue('default');

        } else {
            return $this->redirectWithStatus($request,'inventory.movingItems',"No items could be added, as the item list is empty.", 'warnings');
        }

        return $this->redirectWithStatus($request,'inventory.movingItems',"Successfully added items!", 'success');

    }

    public function removeMovingItems(Request $request){
        $id = $request->source_id;
        if(!$id){
            return $this->redirectWithStatus($request,'inventory.movingItems',"Could not find inventory source!", 'error');
        }

        $source = InventorySource::find($id);

        if($source) {
            InventorySource::destroy($id);

            //update stock levels for new stock
            UpdateStockLevels::dispatch($source->location_id)->onQueue('default');
        }

        return $this->redirectWithStatus($request,'inventory.movingItems',"Marked item source as delivered!!", 'success');
    }
}