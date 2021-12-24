<?php

namespace RecursiveTree\Seat\TerminusInventory\Parser;

class FitParser
{
    public function parseFit($fit){
        $ship = preg_match("^\[(\w+[\w ]*\w+), [\w ]*\]",$fit);
        $name = preg_match("^\[\w+[\w ]*\w+, ([\w ]*)\]",$fit);

        //  [^\[](?<item_name>[\w -.]+)(?:, (?<ammo>[\w -]+))?(?: x(?<quantity>\d+))?$

        dd($ship, $name);
    }
}