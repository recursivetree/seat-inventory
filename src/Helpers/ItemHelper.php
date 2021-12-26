<?php

namespace RecursiveTree\Seat\TerminusInventory\Helpers;

use RecursiveTree\Seat\TerminusInventory\Models\StockItem;
use Seat\Eveapi\Models\Sde\InvType;

class ItemHelper
{
    public static function simplifyItemList($item_list): array
    {
        $item_2_amount = [];

        foreach ($item_list as $item){
            if(array_key_exists($item->type_id, $item_2_amount)){
                $item_2_amount[$item->type_id] += $item->amount;
            } else {
                $item_2_amount[$item->type_id] = $item->amount;
            }
        }

        $optimized = [];
        foreach($item_2_amount as $type => $amount) {
            $item = new StockItem();
            $item->type_id = $type;
            $item->amount = $amount;
            $optimized[] = $item;
        }

        return $optimized;
    }

    public static function itemListToMultiBuy($item_list): string
    {
        $lines = [];

        foreach ($item_list as $item){
            $type = InvType::find($item->type_id);
            if($type!=null) {
                $lines[] = "$type->typeName $item->amount";
            } else {
                $lines[] = "unknown-type-$item->type_id $item->amount";
            }
        }

        return implode("\n",$lines);
    }
}