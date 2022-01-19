<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Models\InventoryItem;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Stock;
use Seat\Eveapi\Models\Assets\CorporationAsset;
use Seat\Eveapi\Models\Contracts\CorporationContract;

use Illuminate\Support\Facades\DB;

class UpdateStockLevels implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $location_id;

    public function __construct($location_id){
        $this->location_id = $location_id;
    }


    public function handle()
    {

        $stocks = Stock::where("location_id",$this->location_id)->get();

        //reset stock level caches
        foreach ($stocks as $stock) {
            $stock->available_on_contracts = 0;
            $stock->available_in_hangars = 0;
        }

        //contracts
        $contract_sources = InventorySource::where("location_id",$this->location_id)->where("source_type","contract")->get();

        foreach ($contract_sources as $contract){
            $item_list = ItemHelper::itemListFromQuery($contract->items);
            $item_map = ItemHelper::itemListToTypeIDMap($item_list);

            foreach ($stocks as $stock){
                if($stock->check_contracts != true) continue;

                foreach ($stock->items as $item){
                    $required = $item->amount;

                    if(!array_key_exists($item->type_id,$item_map)){
                        continue 2;//quit inner loop
                    }

                    if($item_map[$item->type_id]<$required){
                        continue 2;//quit inner loop
                    }
                }

                //all items are available
                $stock->available_on_contracts += 1;
                continue 2; // continue outer loop
            }
        }


        //hangar items
        $source_ids = InventorySource::where("location_id",$this->location_id)->where("source_type","corporation_hangar")->pluck('id');
        $items = InventoryItem::whereIn("source_id",$source_ids)->get();
        $item_list = ItemHelper::itemListFromQuery($items);
        $item_map = ItemHelper::itemListToTypeIDMap($item_list);

        //build up demand list considering that some stocks might already be covered by contracts
        $demand_list = [];
        foreach ($stocks as $stock){
            if($stock->check_corporation_hangars != true) continue; //sort out contracts that don't consider hangars

            $stock_numbers_required = $stock->amount - $stock->available_on_contracts;

            $required_items = ItemHelper::itemListFromQuery($stock->items); //creates a new ItemHelper instance, avoiding side effects when changing the amount
            foreach ($required_items as $item){
                $item->amount *= $stock_numbers_required;
            }

            $demand_list = array_merge($demand_list,$required_items);
        }
        $demand_map = ItemHelper::itemListToTypeIDMap($demand_list);
        $bonus_map = []; // if the proportional scheduling has leftovers, store them for use with other stocks

        error_log(json_encode($demand_map));

        //calculate stock level
        foreach ($stocks as $stock) {
            if ($stock->check_corporation_hangars != true) continue; //sort out contracts that don't consider hangars

            $stock_numbers_required = $stock->amount - $stock->available_on_contracts;
            $stock_numbers_possible = $stock_numbers_required;

            foreach ($stock->items as $item){
                $total_available = array_key_exists($item->type_id, $item_map) ? $item_map[$item->type_id] : 0;
                $item_demand = array_key_exists($item->type_id, $demand_map) ? $demand_map[$item->type_id] : 1;
                $possible_percentage =  $total_available / $item_demand;

                //error_log("$possible_percentage $total_available $item_demand");

                if($possible_percentage < 1){
                    $items_required = $item->amount * $stock_numbers_required;

                    $bonus = array_key_exists($item->type_id,$bonus_map)? $bonus_map[$item->type_id] : 0;
                    $exact_available = ($items_required * $possible_percentage) + $bonus;

                    $available = floor($exact_available);
                    $missing = $items_required - $available;
                    $item->missing_items = $missing;

                    $fulfilled = intdiv($available, $item->amount);
                    if($fulfilled < $stock_numbers_possible){
                        $stock_numbers_possible = $fulfilled;
                    }

                    $bonus = fmod($exact_available, 1.0);
                    $bonus_map[$item->type_id] = $bonus;


                    error_log("$items_required $exact_available $available $missing $fulfilled $bonus");
                } else {
                    $item->missing_items = 0;
                }
                $item->save();
            }

            $stock->available_in_hangars = $stock_numbers_possible;
        }


        //save stocks with cached data
        foreach ($stocks as $stock){
            $stock->save();
        }

    }
}