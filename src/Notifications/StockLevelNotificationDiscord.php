<?php

namespace RecursiveTree\Seat\Inventory\Notifications;

use Seat\Notifications\Notifications\AbstractDiscordNotification;
use Seat\Notifications\Notifications\AbstractNotification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbed;
use Seat\Notifications\Services\Discord\Messages\DiscordEmbedField;
use Seat\Notifications\Services\Discord\Messages\DiscordMessage;

class StockLevelNotificationDiscord extends AbstractDiscordNotification implements ShouldQueue
{
    use SerializesModels;

    private $stocks;

    public function __construct($stocks){
        $this->stocks = $stocks;
    }

    protected function populateMessage(DiscordMessage $message, $notifiable)
    {
        $date = now();
        $message->content("Some stocks are running low! Updated at: $date");

        $message->embed(function (DiscordEmbed $embed){
            $embed->title("View on SeAT");
            $embed->url =  route("inventory.dashboard");
            foreach ($this->stocks as $stock){
                $embed->field(function (DiscordEmbedField $field) use ($stock) {
                    $name = $stock->name;
                    $location = $stock->location->name;
                    $amount = $stock->getTotalAvailable();
                    $threshold = $stock->warning_threshold;
                    $max = $stock->amount;

                    $field->name("$name @ $location");
                    $field->value("in stock: $amount | threshold: $threshold | max: $max");
                });
            }
        });
    }
}