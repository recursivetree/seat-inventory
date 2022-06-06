<?php

namespace RecursiveTree\Seat\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Notification;
use Seat\Notifications\Models\NotificationGroup;
use RecursiveTree\Seat\Inventory\Models\Stock;

class SendStockLevelNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags()
    {
        return [ "notifications", "seat-inventory", "stock-levels" ];
    }

    public function __construct(){

    }


    public function handle()
    {
        $stocks = Stock::all();

        $stocks = $stocks->filter(function ($stock){
            if($stock->getTotalAvailable()<$stock->warning_threshold){
                return true;
            }
            return false;
        });

        if(!$stocks->isEmpty()){
            $this->dispatchNotification($stocks);
        }
    }

    //stolen from https://github.com/eveseat/notifications/blob/master/src/Observers/UserObserver.php
    private function dispatchNotification($stocks){
        $handlers = config('notifications.alerts.seat_inventory_low_stock_levels.handlers', []);

        $routes = $this->getRoutingCandidates();

        // in case no routing candidates has been delivered, exit
        if ($routes->isEmpty())
            return;

        // attempt to enqueue a notification for each routing candidates
        $routes->each(function ($integration) use ($handlers, $stocks) {
            if (array_key_exists($integration->channel, $handlers)) {

                // extract handler from the list
                $handler = $handlers[$integration->channel];

                // enqueue the notification
                Notification::route($integration->channel, $integration->route)
                    ->notify(new $handler($stocks));
            }
        });
    }

    //stolen from https://github.com/eveseat/notifications/blob/master/src/Observers/UserObserver.php
    private function getRoutingCandidates(){
        $settings = NotificationGroup::with('alerts')
            ->whereHas('alerts', function ($query) {
                $query->where('alert', 'seat_inventory_low_stock_levels');
            })->get();

        $routes = $settings->map(function ($group) {
            return $group->integrations->map(function ($channel) {

                // extract the route value from settings field
                $settings = (array) $channel->settings;
                $key = array_key_first($settings);
                $route = $settings[$key];

                // build a composite object built with channel and route
                return (object) [
                    'channel' => $channel->type,
                    'route' => $route,
                ];
            });
        });

        return $routes->flatten()->unique(function ($integration) {
            return $integration->channel . $integration->route;
        });
    }
}