<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class InventorySource extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_inventory_source';

    public function location(){
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class,"source_id","id");
    }
}