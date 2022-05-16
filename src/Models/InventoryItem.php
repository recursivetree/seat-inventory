<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;
use Seat\Eveapi\Models\Sde\InvType;

class InventoryItem extends Model implements ItemEntry
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_inventory_item';

    public function source(){
        return $this->hasOne(InventorySource::class, "id", "source_id");
    }

    public function type(){
        return $this->hasOne(InvType::class, 'typeID', 'type_id')->withDefault([
            "typeName" => "Unknown"
        ]);
    }

    public function getTypeId()
    {
        return $this->type_id;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}