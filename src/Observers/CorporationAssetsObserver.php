<?php

namespace RecursiveTree\Seat\TerminusInventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;

use RecursiveTree\Seat\TerminusInventory\Models\InventoryItem;
use RecursiveTree\Seat\TerminusInventory\Models\InventorySource;
use Seat\Eveapi\Models\Assets\CorporationAsset;

class CorporationAssetsObserver
{
    public function created(CorporationAsset $asset){
        DB::transaction(function () use ($asset) {
            $current_parent = $asset;
            while (true) {
                $parent = $current_parent->container;

                //there is a default returned, so we have to check different
                if ($parent->type_id == null) {
                    break;
                }

                $current_parent = $parent;
            }

            $station = $current_parent->station->station_id;
            $structure = $current_parent->structure->strucutre_id;

            if ($station != null) {
                $location = InventorySource::where("station_id", $station)->first();
                if ($location == null) {
                    $location = new InventorySource();
                    $location->source_type = 'corporation_hangar';
                    $location->source_name = "corporation hangar";
                    $location->station_id = $station;
                    $location->save();
                }
            } elseif ($structure != null) {
                $location = InventorySource::where("structure_id", $structure)->first();
                if ($location == null) {
                    $location = new InventorySource();
                    $location->source_type = 'corporation_hangar';
                    $location->source_name = "corporation hangar";
                    $location->structure_id = $structure;
                    $location->save();
                }
            } else {
                throw new Exception("no location found");
            }

            $item = InventoryItem::where("source_id",$location->id)->where("type_id",$asset->type_id)->first();
            if($item == null){
                $item = new InventoryItem();
                $item->source_id = $location->id;
                $item->type_id = $asset->type_id;
                $item->amount = 0;
            }
            $item->amount += $asset->quantity;

            $item->save();
        });
    }

    public function deleted(CorporationAsset $asset){

    }
}