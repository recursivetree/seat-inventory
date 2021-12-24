<?php

namespace RecursiveTree\Seat\TerminusInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class FittingItem extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_terminusinv_fit_items';

    public function fit(){
        return $this->hasOne(FittingStock::class, "id", "fitting_id");
    }
}