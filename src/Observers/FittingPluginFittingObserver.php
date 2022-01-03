<?php

namespace RecursiveTree\Seat\TerminusInventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\TerminusInventory\Helpers\ItemHelper;
use RecursiveTree\Seat\TerminusInventory\Helpers\Parser;
use RecursiveTree\Seat\TerminusInventory\Models\Stock;
use RecursiveTree\Seat\TerminusInventory\Models\StockItem;

class FittingPluginFittingObserver
{
    public function saved($fitting){
        //search for linked stocks
        $stocks = Stock::where("fitting_plugin_fitting_id",$fitting->id)->get();

        //change every stock
        foreach ($stocks as $stock) {
            //parse the fit
            try {
                $fit = Parser::parseFit($fitting->eftfitting);
            } catch (Exception $e){
                //if parsing fails, use the name of the stock as a way to display the error
                $m = $e->getMessage();
                $stock->name = "[Out Of Sync] $stock->name | Error: $m";
                $stock->save();
                return;
            }

            //after parsing the fit, simplify it
            $items = ItemHelper::simplifyItemList($fit["items"]);

            //change the db
            DB::transaction(function () use ($fit, $stock, $items) {

                // update the name
                $stock->name = $fit["name"];
                $stock->save();

                //get the id to link items
                $id=$stock->id;

                //first, remove all old items
                StockItem::where("stock_id",$id)->delete();

                //inset new items
                foreach ($items as $item_helper){
                    $item = $item_helper->asStockItem();
                    $item->stock_id = $id;
                    $item->save();
                }
            });
        }
    }
}