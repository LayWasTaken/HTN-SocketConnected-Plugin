<?php
namespace HTNProtocol;

use HTNProtocol\Packets\Config\StrictlyAccept;
use HTNProtocol\Streams\Extra\Machine;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
    public $sock;
    public function onEnable():void
    {
        new ClientSocket("localhost", 8080, "09302368772", "Pocketmine", $this,$this->sock);
        // new StrictlyAccept($this);
        // $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onDisable():void
    {
        socket_close($this->sock);
    }
}
