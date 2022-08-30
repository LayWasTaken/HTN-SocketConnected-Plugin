<?php
namespace HTNProtocol;

use HTNProtocol\Events\DataReceivedEvent;
use HTNProtocol\Events\RequestReceivedEvent;
use HTNProtocol\Models\PlayerEvent;
use HTNProtocol\Models\PlayerMessageEvent;
use HTNProtocol\Models\Requests\PlayerPunish;
use PDO;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use poggit\libasynql\DataConnector;

class Events implements Listener
{
    public function __construct(
        private ClientSocket $sock,
        private DataConnector $db
    ) {
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $p = $event->getPlayer();
        if (!$p->hasPlayedBefore()) {
            $this->db->executeInsert("players.insert", [
                "xuid" => $p->getXuid(),
                "name" => $p->getName(),
            ]);
        }
        $this->sock->sendData(
            new \HTNProtocol\Models\PlayerEvent(
                "join",
                $p->getName(),
                null,
                !$p->hasPlayedBefore()
            ),
            "all"
        );
    }
    public function onQuit(PlayerQuitEvent $event)
    {
        $p = $event->getPlayer();
        $this->sock->sendData(
            new \HTNProtocol\Models\PlayerEvent(
                "quit",
                $p->getName(),
                null,
                !$p->hasPlayedBefore()
            ),
            "all"
        );
    }
    public function onMessage(PlayerChatEvent $event)
    {
        $this->sock->sendData(
            new PlayerMessageEvent(
                $event->getPlayer()->getName(),
                $event->getMessage()
            ),
            "DiscordBot"
        );
    }
    public function onDataReceive(RequestReceivedEvent $event)
    {
        $data = $event->getData();
        if ($data instanceof PlayerPunish) {
            $this->sock->sendResponse("DiscordBot", $event->getId());
        }
    }
}
