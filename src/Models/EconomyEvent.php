<?php
namespace HTNProtocol\Models;

use HTNProtocol\Exceptions\InvalidSetException;

class EconomyEvent
{
    public string $player;
    public ?string $receiver;
    public string $event;
    public ?string $item;
    public ?int $amount;
    public int $money;
    public function __set($name, $value)
    {
        if ($name === "event") {
            if (
                !(
                    $value === "auction" ||
                    $value === "pay" ||
                    $value === "shop" ||
                    $value === "sold"
                )
            ) {
                throw new InvalidSetException($name, $value, "Invalid event");
            }
        }
    }
    public static function createAuctionEvent(
        string $item,
        int $amount,
        int $money,
        string $receiver,
        string $player
    ) {
        $event = new self();
        $event->type = "auction";
        $event->item = $item;
        $event->player = $player;
        $event->amount = $amount;
        $event->money = $money;
        $event->receiver = $receiver;
        return $event;
    }
    public static function createPlayerPayEvent(
        int $money,
        string $receiver,
        string $player
    ) {
        $event = new self();
        $event->type = "pay";
        $event->money = $money;
        $event->receiver = $receiver;
        $event->player = $player;
        return $event;
    }
    public static function createShopEvent(
        string $item,
        int $amount,
        int $money,
        string $receiver,
        string $player
    ) {
        $event = new self();
        $event->type = "shop";
        $event->item = $item;
        $event->player = $player;
        $event->amount = $amount;
        $event->money = $money;
        $event->receiver = $receiver;
        return $event;
    }
}
