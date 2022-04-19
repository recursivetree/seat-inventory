<?php

namespace RecursiveTree\Seat\Inventory\Observers;



use RecursiveTree\Seat\Inventory\Jobs\UpdateCategoryMembers;

class FittingPluginDoctrineObserver
{
    public function saved($doctrine){
        //due to the inner workings of seat-fitting, the observer is triggerred before the fittings are saved
        UpdateCategoryMembers::dispatch()->delay(carbon()->addSeconds(10));
    }

    public function created($doctrine){
        //due to the inner workings of seat-fitting, the observer is triggerred before the fittings are saved
        UpdateCategoryMembers::dispatch()->delay(carbon()->addSeconds(10));
    }

    public function deleted($doctrine){
        UpdateCategoryMembers::dispatch();
    }
}