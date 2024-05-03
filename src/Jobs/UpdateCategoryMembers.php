<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;
use Illuminate\Queue\Middleware\WithoutOverlapping;


class UpdateCategoryMembers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("seat-inventory-doctrine-update-sync"))->dontRelease(),
        ];

    }

    public function tags()
    {
        return ["seat-inventory", "categories", "filters"];
    }

    public function handle()
    {
        //get categories
        $categories = StockCategory::where("filters", "!=", null)->get();
        //get stocks
        $stocks = Stock::all();
        foreach ($categories as $category) {
            $category->updateMembers($stocks);
        }
    }
}