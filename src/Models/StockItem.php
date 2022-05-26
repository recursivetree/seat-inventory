<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;

class StockItem extends Model implements ItemEntry
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_stock_items';

    public function stock(){
        return $this->hasOne(Stock::class, "id", "stock_id");
    }

    public function type(){
        return $this->hasOne(InvType::class, 'typeID', 'type_id');
    }

    public function getTypeId()
    {
        return $this->type_id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public static function fromAlternativeAmountColumn($items, $amount_column): ItemEntryList
    {
        return ItemEntryList::fromItemEntries(
            $items->map(function ($item) use ($amount_column) {
                return new ItemEntryBasic($item->type_id,$item[$amount_column]);
            })->values()
        );
    }
}