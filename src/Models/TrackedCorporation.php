<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class TrackedCorporation extends Model
{
    public $timestamps = false;

    public function corporation(){
        return $this->hasOne(CorporationInfo::class, "corporation_id", "corporation_id")->withDefault([
            'name' => trans('web::seat.unknown'),
        ]);
    }

    protected $table = 'recursive_tree_seat_inventory_tracked_corporations';
}