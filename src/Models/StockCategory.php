<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_stock_categories';

    public function stocks(){
        return $this->belongsToMany(
            Stock::class,
            "recursive_tree_seat_inventory_stock_category_mapping",
            "category_id",
            "stock_id"
        );
    }

    public function location(){
        return $this->hasOne(Location::class, 'category_id', 'id');
    }
}