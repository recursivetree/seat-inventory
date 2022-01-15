<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

use RecursiveTree\Seat\Inventory\Models\InventoryItem;
use RecursiveTree\Seat\Inventory\Models\StockItem;
use Seat\Eveapi\Models\Sde\InvType;

class ItemHelper
{
    public int $type_id;
    public int $amount;

    public function __construct($type_id, $amount){
        $this->type_id = $type_id;
        $this->amount = $amount;
    }

    public function name(){
        $type = InvType::find($this->type_id);
        if($type!=null) {
            return $type->typeName;
        } else {
            return "unknown-item-$this->type_id";
        }
    }

    public function asStockItem(){
        $item = new StockItem();
        $item->type_id = $this->type_id;
        $item->amount = $this->amount;
        return $item;
    }

    public function asSourceItem(){
        $item = new InventoryItem();
        $item->type_id = $this->type_id;
        $item->amount = $this->amount;
        return $item;
    }

    public static function simplifyItemList($item_list)
    {
        $item_2_amount = self::itemListToTypeIDMap($item_list);
        return self::typeIDMapToItemList($item_2_amount);
    }

    public static function itemListToMultiBuy($item_list): string
    {
        $lines = [];

        foreach ($item_list as $item){
            $name = $item->name();
            $lines[] = "$name $item->amount";
        }

        return implode("\n",$lines);
    }

    public static function itemListFromQuery($items){
        return $items->map(function ($entry){
            return new ItemHelper($entry->type_id, $entry->amount);
        })->toArray();
    }

    public static function itemListToTypeIDMap($item_list): array {
        $item_2_amount = [];

        foreach ($item_list as $item){
            if(array_key_exists($item->type_id, $item_2_amount)){
                $item_2_amount[$item->type_id] += $item->amount;
            } else {
                $item_2_amount[$item->type_id] = $item->amount;
            }
        }
        return $item_2_amount;
    }

    public static function typeIDMapToItemList($type_map){
        $optimized = [];
        foreach($type_map as $type => $amount) {
            $item = new ItemHelper($type, $amount);
            $optimized[] = $item;
        }

        return $optimized;
    }

}