<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

use Exception;
use Seat\Eveapi\Models\Sde\InvType;

class Parser
{
    public static function parseFit($fit){
        $fit = preg_replace('~\R~u', "\n", $fit);

        $items = [
            'item_names'=>[],
            'item_amount'=>[]
        ];


        $matches=[];
        preg_match_all('/^(?<item>[[:alnum:]\' \-]+?)(?:, [[:alnum:]\' \-]+?)?(?: x(?<amount>\d+))?$/mu', $fit, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match){
            $items['item_names'][] = $match['item'];
            if(array_key_exists('amount',$match)){
                $items['item_amount'][] = $match['amount'];
            } else {
                $items['item_amount'][] = 1;
            }
        }

        $matches = [];
        $res = preg_match("/\[([^,]+),[^,]+]/",$fit,$matches);
        if($res!=1) {
            throw new Exception("Missing ship type!");
        }
        $ship = $matches[1];
        //add ship to required items
        $items['item_names'][] = $ship;
        $items['item_amount'][] = 1;

        $matches = [];
        $res = preg_match("/\[[^,]+,([^,]+)]/",$fit, $matches);
        if($res!=1) {
            throw new Exception("Missing ship name!");
        }
        $name = $matches[1];

        return [
            'items' => self::convertToTypeIDList($items),
            'name' => $name
        ];
    }

    public static function convertToTypeIDList($item_list): array
    {
        $type_list = [];

        for ($i=0; $i < count($item_list['item_names']); $i++){

            if($item_list['item_amount'][$i] != "") {
                $amount = intval($item_list['item_amount'][$i]);
                if ($amount == 0) continue;
            } else {
                $amount = 1;
            }

            $item = $item_list['item_names'][$i];
            $result = InvType::where('typeName', $item)->first();
            if($result == null) continue;

            $stock_item = new ItemHelper($result->typeID,$amount);

            $type_list[] = $stock_item;
        }
        return $type_list;
    }
}