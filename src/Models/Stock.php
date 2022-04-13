<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use Intervention\Image\Facades\Image;
use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;

class Stock extends Model
{
    public $timestamps = false;

    protected $table = 'recursive_tree_seat_inventory_stock_definitions';

    protected $hidden = ['icon','pivot'];

    public function location(){
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function items()
    {
        return $this->hasMany(StockItem::class,"stock_id","id");
    }

    public static function fittingName($stock){
        if (FittingPluginHelper::pluginIsAvailable()){
            $fitting = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::find($stock->fitting_plugin_fitting_id);
            if($fitting!=null){
                return $fitting->fitname;
            } else {
                return "could not find fitting";
            }
        }
        return "could not get name";
    }

    public function categories(){
        return $this->belongsToMany(
            StockCategory::class,
            "recursive_tree_seat_inventory_stock_category_mapping",
            "stock_id",
            "category_id"
        );
    }

    public function getIcon(){
        if($this->icon){
            return $this->icon;
        } else {
            GenerateStockIcon::dispatch($this->id,null); //schedule the image to be generated

            $image = Image::make(__DIR__."/../resources/images/generating.png"); //return a default in the meantime
            return $image->encode("data-url");
        }
    }

    public function setIcon($image){
        $this->icon = $image->encode("data-url");
    }
}