<?php

namespace RecursiveTree\Seat\Inventory\Listeners;

use RecursiveTree\Seat\Inventory\Jobs\UpdateCategoryMembers;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\TreeLib\Items\ItemLoad;
use Seat\Services\Items\EveTypeWithAmount;

class DoctrineUpdatedListener
{
    public function handle($update) {
        UpdateCategoryMembers::dispatch();
    }
}