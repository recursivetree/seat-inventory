<?php

namespace RecursiveTree\Seat\Inventory\Models;

class ItemEntryBasic implements ItemEntry
{
    private $type_id;
    private $amount;

    public function __construct($type_id, $amount){
        $this->type_id = $type_id;
        $this->amount = $amount;
    }

    public function getTypeId()
    {
        return $this->type_id;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}