<?php

namespace RecursiveTree\Seat\Inventory\Http\Controllers;

use Exception;
use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;
use RecursiveTree\Seat\Inventory\Jobs\UpdateCategoryMembers;
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
use Intervention\Image\Facades\Image;

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

        $stock->categories()->detach();

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