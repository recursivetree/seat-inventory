<?php

namespace RecursiveTree\Seat\Inventory\Listeners;

use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\TreeLib\Items\ItemLoad;
use Seat\Services\Items\EveTypeWithAmount;

class FittingUpdatedListener
{
    public function handle($update) {
        $fitting = $update->fitting;

        $stocks = Stock::where("fitting_plugin_fitting_id",$fitting->fitting_id)->get();
        if ($stocks->isEmpty()) return;


        $items = collect($fitting->items)
            ->push(new EveTypeWithAmount($fitting->ship_type_id,1))
            ->simplifyItems();


        foreach ($stocks as $stock){
            $stock->name = $fitting->name;
            $stock->saveItems($items);
        }
    }
}