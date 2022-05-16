<?php

namespace RecursiveTree\Seat\Inventory\Models;

interface ItemEntry
{
    public function getTypeId();
    public function getAmount();
}