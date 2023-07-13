<?php

namespace RecursiveTree\Seat\Inventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockItem;
use RecursiveTree\Seat\TreeLib\Parser\FitParser;

class FittingPluginFittingObserver
{
    public function saved($fitting){
        try {
            //search for linked stocks
            $stocks = Stock::where("fitting_plugin_fitting_id", $fitting->id)->get();

            //change every stock
            foreach ($stocks as $stock) {
                //parse the fit. specifically use the fit parser
                $parser_result = FitParser::parseItems($fitting->eftfitting);
                if ($parser_result == null) {
                    //if parsing fails, use the name of the stock as a way to display the error
                    $stock->name = "[Out Of Sync] $stock->name | Failed to parse fit";
                    $stock->save();
                    return;
                }

                //after parsing the fit, simplify it
                $items = $parser_result->items->simplifyItems();
                $name = $parser_result->shipName ?? "A wild error's request to contact the developer";

                //change the db
                DB::transaction(function () use ($name, $stock, $items) {

                    // update the name
                    $stock->name = $name;
                    $stock->save();

                    //get the id to link items
                    $id = $stock->id;

                    //first, remove all old items
                    StockItem::where("stock_id", $id)->delete();

                    //inset new items
                    $stock->items()->saveMany($items->map(function ($item) use ($stock) {
                        $stock_item = new StockItem();
                        $stock_item->stock_id = $stock->id;
                        $stock_item->type_id = $item->typeModel->typeID;
                        $stock_item->amount = $item->amount;
                        return $stock_item;
                    }));
                });
            }
        } catch (Exception $e) {
            logger()->error("[seat-inventory] Observer for seat-fitting Fitting model failed.",["exception"=>$e]);
        }
    }
}