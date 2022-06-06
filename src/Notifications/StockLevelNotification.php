<?php

namespace RecursiveTree\Seat\Inventory\Notifications;

use Seat\Notifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLevelNotification extends AbstractNotification implements ShouldQueue
{
    use SerializesModels;

    private $stocks;

    public function __construct($stocks){
        $this->stocks = $stocks;
    }

    public function via($notifiable)
    {
        return ['mail','slack'];
    }

    public function toMail($notifiable)
    {

        $message = (new MailMessage)
            ->success()
            ->subject("EVE: Your stocks are running low")
            ->greeting("Hello Stock Manager");

        $message->line("(available/threshold/max)");

        foreach ($this->stocks as $stock){
            $name = $stock->name;
            $location = $stock->location->name;
            $amount = $stock->getTotalAvailable();
            $threshold = $stock->warning_threshold;
            $max = $stock->amount;
            $message->line("$name @$location: $amount/$threshold/$max");
        }

        $message->salutation("Regards, the seat-inventory plugin")
            ->action("View on SeAT", route("inventory.dashboard"));

        return $message;
    }

    public function toSlack(){
        $stocks = $this->stocks;

        $date = now();

        return (new SlackMessage)
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