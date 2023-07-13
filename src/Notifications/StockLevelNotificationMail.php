<?php

namespace RecursiveTree\Seat\Inventory\Notifications;

use Seat\Notifications\Notifications\AbstractMailNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLevelNotificationMail extends AbstractMailNotification implements ShouldQueue
{
    use SerializesModels;

    private $stocks;

    public function __construct($stocks){
        $this->stocks = $stocks;
    }

    public function populateMessage(MailMessage $message, $notifiable)
    {

        $message
            ->success()
            ->subject("EVE: Your stocks are running low")
            ->greeting("Hello Stock Manager");

        $message->line("(available/threshold/max)");

        foreach ($this->stocks as $stock){
            $name = $stock->name;
            $location = $stock->location->name;
            $amount = $stock->available;
            $threshold = $stock->warning_threshold;
            $max = $stock->amount;
            $message->line("$name @$location: $amount/$threshold/$max");
        }

        $message->salutation("Regards, the seat-inventory plugin")
            ->action("View on SeAT", route("inventory.dashboard"));

        return $message;
    }
}