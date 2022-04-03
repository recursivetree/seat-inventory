<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

class ContractHelper
{
    use DescribeItems;

    public static function getDescriptiveContractName($contract){
        $parts = [];

        $description = self::describeItemList($contract->lines)->groupBy("importance")->sortKeysDesc();
        $important_items = $description->first();

        //dd($description);

        if($important_items){
            foreach ($important_items->sortBy("price")->take(3) as $item){
                $parts[] = $item["item"]->typeName;
            }
        } else {
            $parts[] = "empty contract";
        }

        if($contract->price != null && $contract->price > 0) {
            $parts[] = self::approximateNumber($contract->price);
        }

        return implode(" ", array_filter($parts));
    }

    private static function approximateNumber($number){
        $number = ceil($number);

        if($number < 1000) {
            return "$number";
        } elseif ($number < 1000000){
            $adjusted = ceil($number / 1000);
            return $adjusted . "K";
        } elseif ($number < 1000000000){
            $adjusted = ceil($number / 1000000);
            return $adjusted . "M";
        } else {
            $adjusted = ceil($number / 1000000000);
            return $adjusted . "B";
        }
    }
}