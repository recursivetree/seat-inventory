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

    public function tags()
    {
        return [ 'location:' . $this->location_id, "seat-inventory", "stock-levels" ];
    }

    private $location_id;

    public function __construct($location_id){
        $this->location_id = $location_id;
    }


    public function handle()
    {

        $stocks = Stock::with("items")->where("location_id",$this->location_id)->orderBy("priority","desc")->get();

        //get the time from around when the query is triggered, so in case the db updates in between, we make sure that at least some ca be from the old state
        $time = now();

        //reset stock level caches
        foreach ($stocks as $stock) {
            $stock->available_on_contracts = 0;
            $stock->available_in_hangars = 0;
            $stock->last_updated = $time;
        }

        //contracts
        $contract_sources = InventorySource::where("location_id",$this->location_id)->where("source_type","contract")->get();

        //because the contracts are sorted by priority, we don't have to handle it here
        foreach ($contract_sources as $contract){
            $item_list = ItemHelper::itemListFromQuery($contract->items);
            $total_items_map = ItemHelper::itemListToTypeIDMap($item_list);

            foreach ($stocks as $stock){
                if($stock->check_contracts != true) continue;

                //if we already fulfill a stock, don't consider it any further
                if($stock->available_on_contracts>=$stock->amount) continue;

                foreach ($stock->items as $item){
                    $required = $item->amount;

                    if(!array_key_exists($item->type_id,$total_items_map)){
                        continue 2;//quit inner loop
                    }

                    if($total_items_map[$item->type_id]<$required){
                        continue 2;//quit inner loop
                    }
                }

                //all items are available
                $stock->available_on_contracts += 1;
                continue 2; // use this contract for this stock, therefore continue with the next contract
            }
        }


        //hangar items
        $source_ids = InventorySource::where("location_id",$this->location_id)
            ->whereIn("source_type",["corporation_hangar","in_transport"])
            ->pluck('id');
        $items = InventoryItem::whereIn("source_id",$source_ids)->get();
        $item_list = ItemHelper::itemListFromQuery($items);


        $total_items_map = ItemHelper::itemListToTypeIDMap($item_list);
        $used_items_map = $total_items_map;

        $priority_sorted = $stocks->groupBy("priority")->sortKeysDesc();

        $bonus_map = [];

        foreach ($priority_sorted as $stock_list){
            $total_items_map = $used_items_map;

            //build up demand list considering that some stocks might already be covered by contracts
            // this is done per priority
            $demand_list = [];
            foreach ($stock_list as $stock){
                if($stock->check_corporation_hangars != true) continue; //sort out contracts that don't consider hangars

                //parts might already be covered over contracts
                $stock_numbers_required = $stock->amount - $stock->available_on_contracts;

                //make sure we never have a negative requirements
                if($stock_numbers_required < 0){
                    $stock_numbers_required = 0;
                }

                //get items
                $required_items = ItemHelper::itemListFromQuery($stock->items); //creates a new ItemHelper instance, avoiding side effects when changing the amount
                foreach ($required_items as $item){
                    $item->amount *= $stock_numbers_required;
                }

                //merge all required items into one list
                $demand_list = array_merge($demand_list,$required_items);
            }
            $demand_map = ItemHelper::itemListToTypeIDMap($demand_list);

            //calculate stock level
            foreach ($stock_list as $stock) {
                if ($stock->check_corporation_hangars != true) continue; //sort out contracts that don't consider hangars

                $stock_numbers_required = $stock->amount - $stock->available_on_contracts;  //number of stocks required
                if($stock_numbers_required<0){
                    $stock_numbers_required = 0;
                }
                $stock_numbers_possible = $stock_numbers_required;                          //max number of stock multiples you can possibly assemble

                foreach ($stock->items as $item){

                    $total_available = array_key_exists($item->type_id, $total_items_map) ? $total_items_map[$item->type_id] : 0;
                    $item_demand = array_key_exists($item->type_id, $demand_map) ? $demand_map[$item->type_id] : 1;
                    $possible_percentage =  $total_available / $item_demand;
                    $items_required = $item->amount * $stock_numbers_required;

                    if($possible_percentage < 1){
                        $bonus = array_key_exists($item->type_id,$bonus_map)? $bonus_map[$item->type_id] : 0;
                        $exact_available = ($items_required * $possible_percentage) + $bonus;

                        $available = floor($exact_available);
                        $missing = $items_required - $available;
                        $item->missing_items = $missing;

                        if(array_key_exists($item->type_id,$used_items_map)) {
                            $used_items_map [$item->type_id] -= $available;
                            if($used_items_map [$item->type_id]<0){
                                $used_items_map [$item->type_id] = 0;
                            }
                        }

                        $fulfilled = intdiv($available, $item->amount);
                        if($fulfilled < $stock_numbers_possible){
                            $stock_numbers_possible = $fulfilled;
                        }

                        $bonus = fmod($exact_available, 1.0);
                        $bonus_map[$item->type_id] = $bonus;

                    } else {
                        $item->missing_items = 0;
                        $used_items_map[$item->type_id] -= $items_required;
                    }
                    $item->save();
                }

                $stock->available_in_hangars = $stock_numbers_possible;
                $stock->save();
            }
        }
    }
}