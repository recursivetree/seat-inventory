<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Models\Sde\InvType;

class StockItem extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_stock_items';

    public function stock(){
        return $this->hasOne(Stock::class, "id", "stock_id");
    }

    public function type(){
        return $this->hasOne(InvType::class, 'typeID', 'type_id');
    }
}