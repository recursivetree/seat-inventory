<?php

namespace RecursiveTree\Seat\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;
use RecursiveTree\Seat\TreeLib\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Jobs\GenerateStockIcon;

class Stock extends Model
{
    public const TABLE = 'seat_inventory_stocks';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $hidden = ['icon'];

    public function location(){
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

    public function items()
    {
        return $this->hasMany(StockItem::class,"stock_id","id");
    }

    public function levels(){
        return $this->hasMany(StockLevel::class,"stock_id","id");
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
            "seat_inventory_stock_category_mapping",
            "stock_id",
            "category_id"
        )->withPivot('manually_added','category_eligible');
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

    public function getTotalAvailable(){
        return $this->available;
    }

    public function isEligibleForCategory($filters){
        $filters = json_decode($filters);

        $has_location = false;
        $location_fulfilled = false;

        $has_doctrine = false;
        $doctrine_fulfilled = false;

        foreach ($filters as $filter){
            if($filter->type === "location"){
                $has_location = true;
                if($this->location_id === $filter->id) $location_fulfilled = true;

            } else if($filter->type === "doctrine"){
                $has_doctrine = true;
                if(!FittingPluginHelper::pluginIsAvailable()){
                    $doctrine_fulfilled = true;
                } else {
                    $fitting = FittingPluginHelper::$FITTING_PLUGIN_FITTING_MODEL::find($this->fitting_plugin_fitting_id);
                    if ($fitting) {
                        if ($fitting->doctrines()->where("id", $filter->id)->exists()) $doctrine_fulfilled = true;
                    }
                }
            }
        }

        return
            ($has_location || $has_doctrine) //only eligible if we have filters
            &&(!$has_location || $location_fulfilled) //location
            && (!$has_doctrine || $doctrine_fulfilled); //doctrine
    }
}