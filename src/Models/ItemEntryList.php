<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Seat\Eveapi\Models\Sde\InvType;

class ItemEntryList
{
    private $items;

    private function __construct($items){
        $this->items = $items;
    }

    public static function fromItemEntries($item_entries){
        return new ItemEntryList($item_entries->filter(function ($item){
            return $item->getAmount() > 0;
        })->map(function ($item){
            return new ItemEntryBasic($item->getTypeId(), $item->getAmount());
        })->values());
    }

    public function simplify(){
        $this->items = $this->items->groupBy(function ($item){
            return $item->getTypeId();
        })->map(function ($items,$key){
            $amount = 0;

            foreach ($items as $item){
                $amount += $item->getAmount();
            }

            return new ItemEntryBasic($key,$amount);
        })->values();;
    }

    public function asJsonStructure(){
        return $this->items->map(function ($item){
            $type_id = $item->getTypeId();
            $name = InvType::find($type_id)->typeName;

            return [
                "type_id" => $type_id,
                "amount" => $item->getAmount(),
                "name" => $name
            ];
        });
    }
}