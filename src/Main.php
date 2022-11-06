<?php
namespace HTNProtocol;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Uuid;
use SOFe\AwaitGenerator\Await;
use SOFe\AwaitStd\AwaitStd;

class Main extends PluginBase implements Listener {
    /**
     * @var string[] $LastCrashes
     */
    private array $LastCrashes = [];
    private DataConnector $db;
    private ClientSocket $sock;
    private Config $config;

    public function onEnable(): void {
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);
        $this->initDatabase();
        $this->sock = new ClientSocket($this);
        $sock_config = $this->config->get("socket");
        $this->sock->start($sock_config["ip"], $sock_config["port"], $sock_config["token"]);
    }
    public function onCommand(
        CommandSender $sender,
        Command $command,
        string $label,
        array $args,
    ): bool {
        if ($sender instanceof Player) {
            if ($command->getName() === 'connectdc') {
                if (!array_key_exists(0, $args)) {
                    $sender->sendMessage(
                        'Must add a discord tag or id to connect',
                    );
                    return false;
                }
                $code = mt_rand(1000, 999999);
                $this->db->executeInsert(
                    'discord_codes.insert',
                    [
                        'xuid' => $sender->getXuid(),
                        'discord' => $args[0],
                        'code' => $code,
                    ],
                    function () use ($sender, $code) {
                        $sender->sendMessage(
                            "You can only generate a new code every 12 days so pls remember it\nCode: $code",
                        );
                    },
                    function ($err) use ($sender) {
                        var_dump($err);
                        $sender->sendMessage(
                            'Something went wrong try again later',
                        );
                    },
                );
            }
        }
        return true;
    }
    public function onDisable(): void {}

    private function initDatabase(){
        $database_config = $this->config->get("database");
        $this->db = libasynql::create(
            $this,
            [
                "type" => "mysql",
                "mysql" => $database_config["connection_config"],
                "worker_limit" => $database_config["worker-limit"]
            ],
            [
                'mysql' => 'mysql.sql',
            ]
        );
        $this->db->executeGeneric('players.init');
        $this->db->executeGeneric('discord_codes.init');
        $this->LastCrashes = array_filter(
            scandir(
                str_replace('plugins\HTNProtocol\src', 'crashdumps', __DIR__)
            ),
            fn($v) => str_contains($v, '.log')
        );
    }

    private function addCrashToDB(){
        $currCrashes = array_values(
            scandir(
                str_replace('plugins\HTNProtocol\src', 'crashdumps', __DIR__),
                SCANDIR_SORT_DESCENDING,
            ),
        );
        $crash =
            $currCrashes[0] === '.last_crash'
                ? $currCrashes[1]
                : $currCrashes[0];
        if (
            count(
                array_filter($currCrashes, fn($v) => str_contains($v, '.log')),
            ) > count($this->LastCrashes)
        ) {
            $file = @fopen(
                str_replace('plugins\HTNProtocol\src', 'crashdumps', __DIR__) .
                    "\\$crash",
                'r',
                true,
            );
            if ($file) {
                $crash = '';
                while (($line = fgets($file)) !== false) {
                    if (
                        strpos($line, 'Error: ') === 0 ||
                        strpos($line, 'File: ') === 0 ||
                        strpos($line, 'Line: ') === 0 ||
                        strpos($line, 'Type: ') === 0
                    ) {
                        $crash .= $line;
                    }
                    if (strpos($line, 'Type: ') === 0) {
                        break;
                    }
                }
                fclose($file);
            }
        }
        return $crash;
    }
}
