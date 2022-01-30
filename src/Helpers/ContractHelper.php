<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

class ContractHelper
{
    public static function getDescriptiveContractName($contract){
        $ship = null;
        $price = 0;
        $groups = [];

        foreach ($contract->lines as $item){
            $price += $item->type->price->adjusted_price * $item->quantity;

            if($item->type->group->categoryID==6) {
                if($ship != null){
                    if($ship->price->adjusted_price < $item->type->price->adjusted_price){
                        $ship = $item->type;
                    }
                } else {
                    $ship = $item->type;
                }
            }

            $groups[] = $item->type->group;
        }

        $parts = [];

        if($ship){
            $parts[] = $ship->typeName;
        } else {
            $groups = array_unique($groups);

            if(count($groups) > 3){
                $parts[] = "diverse items";
            } else {
                foreach ($groups as $group){
                    $parts[] = $group->groupName."s";
                }
            }
        }

        if($contract->price != null && $contract->price > 0) {
            $parts[] = self::approximateNumber($contract->price);
        } else {
            $parts[] = self::approximateNumber($price);
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