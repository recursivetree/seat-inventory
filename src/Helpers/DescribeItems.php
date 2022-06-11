<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

/*
 * Utility to help find important items in a stock
 * */

const STRUCTURE = 6;
const SHIP = 5;
const MODULE = 4;
const MODULE_EXTENDED = 3;
const BLUEPRINT = 2;
const INDUSTRY = 1;
const OTHER = 0;
const UNKNOWN = -1;

const CATEGORY_MAP = [
    4 => INDUSTRY, // Materials
    5 => OTHER, //Accessoires
    6 => SHIP, //Ships
    7 => MODULE, //Modules
    8 => MODULE_EXTENDED, //Charges
    9 => BLUEPRINT, //Blueprint
    17 => OTHER, //Commodity
    18 => MODULE_EXTENDED, //Drone
    20 => MODULE_EXTENDED, //Implant
    22 => MODULE_EXTENDED, //Deployables (mtu)
    23 => STRUCTURE, //POS stuff
    24 => BLUEPRINT, //reactions
    25 => INDUSTRY, //asteroid
    30 => OTHER, //apparel
    32 => MODULE, //t3 subsystems
    34 => INDUSTRY, //t3 indy relics
    35 => INDUSTRY, //decryptors
    39 => STRUCTURE, //ihub upgrades
    40 => STRUCTURE, //sov structures
    41 => INDUSTRY, //PI command centers
    42 => INDUSTRY, //unrefined PI
    43 => INDUSTRY, //p1+ pi
    46 => STRUCTURE, //customs offices
    63 => OTHER, //rare special edition items
    65 => STRUCTURE, //structures
    66 => MODULE, //structure modules
    87 => MODULE_EXTENDED, //fighters
    91 => OTHER, //skins
    2100 => OTHER, //expert systems
];

const ITEM_MAP = [
    4358 => MODULE_EXTENDED,//exotic dancer, male
    17765 => MODULE_EXTENDED,//exotic dancer, female
];

trait DescribeItems
{

    public static function describeItemList($items){
        $categorized_item = [];

        foreach ($items as $item){

            $importance = UNKNOWN;

            if(array_key_exists($item->type->group->categoryID,CATEGORY_MAP)){
                $importance = CATEGORY_MAP[$item->type->group->categoryID];
            }

            if(array_key_exists($item->type->typeID,ITEM_MAP)){
                $importance = ITEM_MAP[$item->type->typeID];
            }

            $categorized_item[] = [
                "importance"=>$importance,
                "item" => $item->type,
                "price" => $item->type->price->adjusted_price,
            ];
        }

        return collect($categorized_item)->sortByDesc("price")->sortByDesc("importance");
    }
}