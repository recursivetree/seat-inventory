<?php

namespace RecursiveTree\Seat\TerminusInventory\Observers;

use Exception;
use Illuminate\Support\Facades\DB;
use RecursiveTree\Seat\TerminusInventory\Helpers\Parser;
use RecursiveTree\Seat\TerminusInventory\Models\Stock;
use RecursiveTree\Seat\TerminusInventory\Models\StockItem;

class FittingPluginFittingObserver
{
    public function saved($fitting){
        $stock = Stock::where("fitting_plugin_fitting_id",$fitting->id)->first();

        if($stock!=null) {
            try {
                $fit = Parser::parseFit($fitting->eftfitting);
            } catch (Exception $e){
                $m = $e->getMessage();
                $stock->name = "[Out Of Sync] $stock->name | Error: $m";
                $stock->save();
                return;
            }

            DB::transaction(function () use ($fit, $stock) {
                $stock->name = $fit["name"];
                $stock->save();

                $id=$stock->id;

                StockItem::where("stock_id",$id)->delete();

                foreach ($fit["items"] as $item){
                    $item->stock_id = $id;
                    $item->save();
                }
            });
        }
    }
}