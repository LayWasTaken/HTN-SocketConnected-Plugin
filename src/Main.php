<?php
namespace HTNProtocol;

use HTNProtocol\Events\RequestReceivedEvent;
use HTNProtocol\Models\ServerCrash;
use HTNProtocol\Models\EconomyEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use WebSocket\Client;

class Main extends PluginBase implements Listener
{
    public ClientSocket $sock;
    /**
     * @var string[] $LastCrashes
     */
    private array $LastCrashes = [];
    public function onEnable(): void
    {
        $this->LastCrashes = array_filter(
            scandir(
                str_replace("plugins\HTNProtocol\src", "crashdumps", __DIR__)
            ),
            fn($v) => str_contains($v, ".log")
        );
        $this->getServer()
            ->getPluginManager()
            ->registerEvents($this, $this);
        $this->sock = new ClientSocket(
            "localhost",
            8080,
            "09302368772",
            "Pocketmine",
            $this
        );
        $this->sock->registerReceivable(
            "\HTNProtocol\Streams\JsonStreamClasses\Requests\PlayerPunish",
            ["DiscordBot"],
            function () {
                echo "FOOO";
            }
        );
    }
    public function onCommand(
        CommandSender $sender,
        Command $command,
        string $label,
        array $args
    ): bool {
        if (array_key_exists(0, $args) && $args[0] === "r") {
            throw new \Exception("ERROR");
            return true;
        }
        return true;
    }
    public function onDisable(): void
    {
        $currCrashes = array_values(
            scandir(
                str_replace("plugins\HTNProtocol\src", "crashdumps", __DIR__),
                SCANDIR_SORT_DESCENDING
            )
        );
        $crash =
            $currCrashes[0] === ".last_crash"
                ? $currCrashes[1]
                : $currCrashes[0];
        if (
            count(
                array_filter($currCrashes, fn($v) => str_contains($v, ".log"))
            ) > count($this->LastCrashes)
        ) {
            $file = @fopen(
                str_replace("plugins\HTNProtocol\src", "crashdumps", __DIR__) .
                    "\\$crash",
                "r",
                true
            );
            if ($file) {
                $crash = "";
                while (($line = fgets($file)) !== false) {
                    if (
                        strpos($line, "Error: ") === 0 ||
                        strpos($line, "File: ") === 0 ||
                        strpos($line, "Line: ") === 0 ||
                        strpos($line, "Type: ") === 0
                    ) {
                        $crash .= $line;
                    }
                    if (strpos($line, "Type: ") === 0) {
                        break;
                    }
                }
                fclose($file);
                echo $crash;
                var_dump($this->sock->sendData(new ServerCrash($crash), "all"));
            }
        }
        $this->sock->closeConnection();
    }

    // public function onDataReceive(RequestReceivedEvent $event)
    // {
    //     $data = $event->getData();
    //     if ($data instanceof PlayerPunish) {
    //         var_dump($data);
    //         $this->sock->sendResponse("DiscordBot", $event->getId(), null);
    //     }
    // }
}
