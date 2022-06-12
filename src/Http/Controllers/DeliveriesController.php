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
use Seat\Web\Http\Controllers\Controller;

class DeliveriesController extends Controller
{
    public function addDeliveries(Request $request){
        $request->validate([
            "items" => "string|required",
            "location"=>"integer|required"
        ]);

        $location_id = $request->location;
        $multibuy = $request->items;

        if (!Location::find($location_id)){
            return response()->json(["message"=>"location not found"],400);
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
            return response()->json(["message"=>"Invalid multibuy: No items added"],400);
        }

        return response()->json(["message"=>"Added"]);
    }

    public function listDeliveries(){
        $deliveries = InventorySource::with("location","items.type")->where("source_type","in_transport")->get();
        return response()->json($deliveries);
    }

    public function deleteDeliveries(Request $request){
        $request->validate([
            "id"=>"integer|required"
        ]);

        $id = $request->id;

        $source = InventorySource::where("id",$id)->where("source_type","in_transport")->first();

        if($source) {
            InventorySource::destroy($id);

            //update stock levels for new stock
            UpdateStockLevels::dispatch($source->location_id)->onQueue('default');
        } else {
            return response()->json(["message"=>"Delivery not found!"],400);
        }

        return response()->json();
    }
}