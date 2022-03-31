<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use RecursiveTree\Seat\Inventory\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Seat\Eveapi\Models\Sde\InvType;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;


class GenerateStockIcon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $stock_id;
    private $ship_type_id;

    private const ICON_SIZE = 512;

    public function __construct($stock_id, $ship_type_id)
    {
        $this->stock_id = $stock_id;
        $this->ship_type_id = $ship_type_id;
    }

    public function tags()
    {
        return ["seat-inventory", "stock","icon"];
    }

    public function handle()
    {
        $stock = Stock::find($this->stock_id);
        if(!$stock) {
            $this->delete();
        }

        $image_type = InvType::find($this->ship_type_id);

        if(!$image_type){
            foreach ($stock->items as $item){
                if($item->type->group->categoryID==6){
                    if($image_type != null){
                        if($image_type->price->adjusted_price < $item->type->price->adjusted_price){
                            $image_type = $item->type;
                        }
                    } else {
                        $image_type = $item->type;
                    }
                }
            }
        }

        $image = null;

        if($image_type){
            try {
                $image = Image::make("https://images.evetech.net/types/$image_type->typeID/render");
                $image = $image->resize(self::ICON_SIZE,self::ICON_SIZE);
            } catch (NotReadableException $e){
                //could not fetch image, leave $image = null, so that
            }
        }

        if(!$image){
            $image = Image::canvas(self::ICON_SIZE,self::ICON_SIZE,"#eee");
        }

        $image = $image->rectangle(0, 427, 512, 512, function ($draw) {
            $draw->background('rgba(150, 150, 150, 0.3)');
        });

        $image = $image->text($stock->name,16,448,function ($font){
            $font->file(__DIR__."/../resources/fonts/Roboto-Regular.ttf");
            $font->valign("top");
            $font->align("left");
            $font->size(48);
            $font->color([255, 255, 255, 1]);
        });

        $stock->setIcon($image);
        $stock->save();
    }
}