<?php

namespace RecursiveTree\Seat\Inventory\Notifications;

use Seat\Notifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Notifications\Notifications\AbstractSlackNotification;

class StockLevelNotificationSlack extends AbstractSlackNotification implements ShouldQueue
{
    use SerializesModels;

    private $stocks;

    public function __construct($stocks){
        $this->stocks = $stocks;
    }

    public function populateMessage(SlackMessage $message, $notifiable){
        $stocks = $this->stocks;

        $date = now();

        return $message
            ->success()
            ->content("Some stocks are running low! Updated at: $date")
            ->from('SeAT Inventory Manager')
            ->attachment(function ($attachment) use ($stocks) {
                $attachment
                    ->title("View on SeAT", route("inventory.dashboard"))
                    ->content("(in stock/threshold/max)");
                foreach ($stocks as $stock){
                    $attachment->field(function ($field) use ($stock) {

                        $name = $stock->name;
                        $location = $stock->location->name;
                        $amount = $stock->getTotalAvailable();
                        $threshold = $stock->warning_threshold;
                        $max = $stock->amount;

                        $field
                            ->long()
                            ->title("$name @ $location")
                            ->content("$amount/$threshold/$max");
                    });
                }
            });

    }
}