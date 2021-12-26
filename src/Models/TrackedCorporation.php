<?php

namespace RecursiveTree\Seat\TerminusInventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class TrackedCorporation extends Model
{
    public $timestamps = false;

    public function corporation(){
        return $this->hasOne(CorporationInfo::class, "corporation_id", "corporation_id");
    }

    protected $table = 'recursive_tree_seat_terminusinv_tracked_corporations';
}