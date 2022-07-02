<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Sde\InvType;

class StockLevel extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_stock_levels';

    public function stock(){
        return $this->hasOne(Stock::class, "id", "stock_id");
    }

    public function source(){
        $sources = config('inventory.sources');

        if(array_key_exists($this->source_type,$sources)){
            return $sources[$this->source_type];
        }

        return null;
    }
}