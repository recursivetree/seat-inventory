<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Universe\UniverseStation;
use Seat\Eveapi\Models\Universe\UniverseStructure;

class Workspace extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_workspaces';
}