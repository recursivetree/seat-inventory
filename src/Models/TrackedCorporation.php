<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class TrackedCorporation extends Model
{
    public $timestamps = false;

    public function corporation()
    {
        return $this->hasOne(CorporationInfo::class, "corporation_id", "corporation_id")->withDefault([
            'name' => trans('web::seat.unknown'),
        ]);
    }

    public function alliance()
    {
        return $this->hasOne(Alliance::class, "alliance_id", "managed_by")->withDefault(["name"=>""]);
    }

    protected $table = 'seat_inventory_tracked_corporations';
    protected $primaryKey = 'corporation_id';
    public $incrementing = false;
}