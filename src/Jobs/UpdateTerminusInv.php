<?php

namespace RecursiveTree\Seat\TerminusInventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use RecursiveTree\Seat\TerminusInventory\Helpers\ItemHelper;
use RecursiveTree\Seat\TerminusInventory\Models\InventoryItem;
use RecursiveTree\Seat\TerminusInventory\Models\InventorySource;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\CorporationContract;

use Illuminate\Support\Facades\DB;

class UpdateTerminusInv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $station_items = [];
    private array $structure_items = [];

    private static array $ITEM_BLACKLIST = [
        27 //Corporation Hangar Office
    ];

    public function handle(){
        DB::transaction(function () {
            $sources = InventorySource::where("source_type","corporation_hangar")->orWhere("source_type","contract")->get();
            foreach ($sources as $source){
                $source->delete();
                InventoryItem::where("source_id", $source->id)->delete();
            }

            $this->handleCorporationAssets();
            $this->handleContracts();
        });
    }

    private function handleContracts(){
        $contracts = CorporationContract::with("detail")->get();

        foreach ($contracts as $contract){
            $details = $contract->detail;
            if($details->type != "item_exchange"){
                continue;
            }
            if($details->status != "outstanding"){
                continue;
            }

            $items = [];

            foreach ( $details->lines as $item){
                $items[] = new ItemHelper($item->type_id,$item->quantity);
            }

            $station_id = $details->end_location->station_id;
            $structure_id = $details->end_location->structure_id;


            $source = new InventorySource();
            if($station_id!=null){
                $source->station_id = $station_id;
            } elseif ($structure_id!=null){
                $source->structure_id = $structure_id;
            } else {
                continue; // contract has no location?
            }

            $source->source_name = "$details->title";
            $source->source_type = "contract";
            $source->save();

            $simplified = ItemHelper::simplifyItemList($items);

            $source_id = $source->id;
            foreach ($simplified as $item_helper){
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }
        //throw new Exception(json_encode(InventoryItem::all()));
    }

    private function handleCorporationAssets(){
        $items = CorporationAsset::all(); //TODO check tracked corporations

        foreach ($items as $item){
            $this->handleAssetItem($item);
        }

        foreach ($this->structure_items as $id => $items){
            $simplified = ItemHelper::simplifyItemList($items);

            $source = new InventorySource();
            $source->source_name = "Structure Hangar";
            $source->source_type = "corporation_hangar";
            $source->structure_id = $id;

            $source->save();

            $source_id = $source->id;
            foreach ($simplified as $item_helper){
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }

        foreach ($this->station_items as $id => $items){
            $simplified = ItemHelper::simplifyItemList($items);

            $source = new InventorySource();
            $source->source_name = "Station Hangar";
            $source->source_type = "corporation_hangar";
            $source->station_id = $id;

            $source->save();

            $source_id = $source->id;

            foreach ($simplified as $item_helper){
                $item = $item_helper->asSourceItem();
                $item->source_id = $source_id;
                $item->save();
            }
        }
    }

    private function handleAssetItem($item){
        if(in_array($item->type_id,self::$ITEM_BLACKLIST)){
            return;
        }

        $current_parent = $item;
        while (true) {
            $parent = CorporationAsset::where("item_id",$current_parent->location_id)->first();

            if ($parent == null) {
                break;
            }

            $current_parent = $parent;
        }

        $item_data = new ItemHelper($item->type_id, $item->quantity);

        if($current_parent->station()->exists()){
            $station = $current_parent->station->station_id;
            if (!array_key_exists($station,$this->station_items)){
                $this->station_items[$station] = [];
            }
            $this->station_items[$station][] = $item_data;
        }
        if($current_parent->structure()->exists()){
            $structure = $current_parent->structure->structure_id;
            if (!array_key_exists($structure,$this->structure_items)){
                $this->structure_items[$structure] = [];
            }
            $this->structure_items[$structure][] = $item_data;
        }
    }
}