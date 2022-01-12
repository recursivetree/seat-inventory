<?php

namespace RecursiveTree\Seat\TerminusInventory\Helpers;

use RecursiveTree\Seat\TerminusInventory\Models\InventoryItem;
use RecursiveTree\Seat\TerminusInventory\Models\InventorySource;
use RecursiveTree\Seat\TerminusInventory\Models\Stock;

class StockHelper
{
    public static function computeStockLevels($location, $targeted_stock){

        $stocks = Stock::where("location_id",$location->id)->get();

        $source_ids = InventorySource::where("location_id",$location->id)->pluck('id');

        $items = InventoryItem::whereIn("source_id",$source_ids)->get();

        $item_list = ItemHelper::itemListFromQuery($items);
        $item_map = ItemHelper::itemListToTypeIDMap($item_list);

        $missing = [];

        foreach ($stocks as $stock){
            if($stock == $targeted_stock) continue;
            foreach ($stock->items as $item){
                $amount = $item->amount * $stock->amount;
                self::subtractItemLike($item_map,$item->type_id,$amount,$missing);
            }
        }
        $missing = ItemHelper::simplifyItemList($missing);

        $target_stock_limit = PHP_INT_MAX;
        $target_missing = [];
        foreach ($targeted_stock->items as $item){
            $amount = $item->amount * $stock->amount;
            if($amount==0) continue;

            if(array_key_exists($item->type_id,$item_map)){
                $possible = floor($item_map[$item->type_id] / $amount);
                if($possible<$target_stock_limit){
                    $target_stock_limit = $possible;
                }
            } else {
                $target_stock_limit = 0;
            }
            self::subtractItemLike($item_map,$item->type_id,$amount,$target_missing);
        }
        $target_missing = ItemHelper::simplifyItemList($target_missing);

        return [
            "missing_items" => $missing,
            "target_amount" => $target_stock_limit,
            "target_missing" => $target_missing,
        ];
    }

    private static function subtractItemLike(&$type_map, $type_id, $amount, &$missing_array){
        if(array_key_exists($type_id,$type_map)){
            $available = $type_map[$type_id];
        } else {
            $available = 0;
        }

        if($available < $amount){
            $type_map[$type_id] = 0;
            $missing_array[] = new ItemHelper($type_id, $amount - $available);
        } else {
            $type_map[$type_id] = $available - $amount;
        }
    }
}