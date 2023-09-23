<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use RecursiveTree\Seat\Inventory\Helpers\ItemHelper;
use RecursiveTree\Seat\Inventory\Models\InventorySource;
use RecursiveTree\Seat\Inventory\Models\Location;
use Seat\Eveapi\Jobs\AbstractAuthCharacterJob;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Eveapi\Models\Universe\UniverseStructure;

/**
 * Class Orders.
 *
 * @package Seat\Eveapi\Jobs\Market
 */
class LoadStructureOrders extends AbstractAuthCharacterJob
{

    /**
     * @var string
     */
    protected $method = 'get';

    /**
     * @var string
     */
    protected $endpoint = '/markets/structures/{structure_id}/';

    /**
     * @var string
     */
    protected $version = 'v1';

    /**
     * @var string
     */
    protected $scope = 'esi-markets.structure_markets.v1';

    /**
     * @var array
     */
    protected $tags = ['market', 'structure'];


    protected $location;
    protected $workspace;

    /**
     * @param RefreshToken $token
     * @param $location
     * @param $workspace
     */
    public function __construct(RefreshToken $token,$location, $workspace)
    {
        parent::__construct($token);
        $this->location = $location;
        $this->workspace = $workspace;
    }


    /**
     * Execute the job.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        //TODO add race condition detection when upgrading to seat 5

        if($this->location->structure_id === null) return;

        $source = InventorySource::where('location_id', $this->location->id)
            ->where('source_type','market')
            ->first();

        if(!$source){
            $source = new InventorySource();
            $source->location_id = $this->location->id;
            $source->source_name = "Market Orders";
            $source->source_type = 'market';
            $source->workspace_id = $this->workspace->id;
            $source->save();
        }

        $items = [];

        //load all market data
        while (true) {
            //retrieve one page of market orders
            $orders = $this->retrieve(['structure_id' => $this->location->structure_id]);

            // process orders
            // if the batch size is increased to 1000, it crashed
            collect($orders)->chunk(100)->each(function ($chunk) use (&$items) {
                foreach ($chunk as $order){
                    $items[] = new ItemHelper($order->type_id, $order->volume_remain);
                }
            });

            // if there are more pages with orders, continue loading them
            if (! $this->nextPage($orders->pages)) break;
        }

        //simplify items
        $items = ItemHelper::simplifyItemList($items);
        foreach ($items as $item_helper) {
            $item = $item_helper->asSourceItem();
            $item->source_id = $source->id;
            $item->save();
        }
    }
}
