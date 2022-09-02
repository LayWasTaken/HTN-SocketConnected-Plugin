<?php
namespace HTNProtocol;

use HTNProtocol\Models\Extra\Machine;
use HTNProtocol\Models\Requests\GetIslandInfo;
use HTNProtocol\Models\Responses\IslandInfo;
use HTNProtocol\Models\Responses\PlayerInfo;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use HTNProtocol\Models\Extra\Member;
use HTNProtocol\Models\Requests\GivePlayer;

class Main extends PluginBase implements Listener {
    public ClientSocket $sock;
    /**
     * @var string[] $LastCrashes
     */
    private array $LastCrashes = [];
    private DataConnector $db;
    public function onEnable(): void {
        $this->config = new Config($this->getDataFolder() . 'config.yml');
        $this->db = libasynql::create(
            $this,
            $this->getConfig()->get('database'),
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
        $this->getServer()
            ->getPluginManager()
            ->registerEvents($this, $this);
        $this->sock = new ClientSocket(
            'localhost',
            8080,
            '09302368772',
            'Pocketmine',
            $this
        );
        $this->sock->registerReceivable(
            'HTNProtocol\Models\Requests\GetPlayerInfo',
            ['DiscordBot'],
            function ($data, $id) {
                $res = new PlayerInfo();
                $res->friends = ['Raze', 'Gamerboy'];
                $res->is_staff = true;
                $res->kill_stat = ['current_place' => 100, 'kills' => 69];
                $res->play_time = 1310402840;
                $res->rank = PlayerInfo::CHALLENGER;
                $res->money = 1940480;
                $res->bank_money = 317328478242482;
                $res->votes = 20;
                $res->xuid = '699939274294792';
                $res->status = Playerinfo::ONLINE;
                $this->sock->sendResponse('DiscordBot', $id, $res);
            }
        );
        $this->sock->registerReceivable(
            '\HTNProtocol\Models\Requests\PlayerPunish',
            ['DiscordBot'],
            function ($data) {
                var_dump($data);
                echo 'FOOO';
            }
        );
        $this->sock->registerReceivable(
            '\HTNProtocol\Models\Requests\GivePlayer',
            ['DiscordBot'],
            function (GivePlayer $request, string $id) {
                $p = $this->getServer()->getPlayerExact($request->player);
                if (!$p) {
                    return $this->sock->sendResponse(
                        'DiscordBot',
                        $id,
                        'Invalid Player'
                    );
                }
                if ($request->give === 'money') {
                    $p->sendMessage(
                        'You have been given ' + $request->amount + ' Money'
                    );
                    $this->sock->sendResponse('DiscordBot', $id);
                    return;
                }
                $p->sendMessage(
                    'You have been given ' +
                        $request->amount +
                        ' ' +
                        $request->give
                );
                $this->sock->sendResponse('DiscordBot', $id);
            }
        );
        $this->getServer()
            ->getPluginManager()
            ->registerEvents(new Events($this->sock, $this->db), $this);
    }
    public function onCommand(
        CommandSender $sender,
        Command $command,
        string $label,
        array $args
    ): bool {
        if ($sender instanceof Player) {
            if ($command->getName() === 'connectdc') {
                if (!array_key_exists(0, $args)) {
                    $sender->sendMessage(
                        'Must add a discord tag or id to connect'
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
                            "You can only generate a new code every 12 days so pls remember it\nCode: $code"
                        );
                    },
                    function ($err) use ($sender) {
                        var_dump($err);
                        $sender->sendMessage(
                            'Something went wrong try again later'
                        );
                    }
                );
            }
        }
        return true;
    }
    public function onDisable(): void {
        $currCrashes = array_values(
            scandir(
                str_replace('plugins\HTNProtocol\src', 'crashdumps', __DIR__),
                SCANDIR_SORT_DESCENDING
            )
        );
        $crash =
            $currCrashes[0] === '.last_crash'
                ? $currCrashes[1]
                : $currCrashes[0];
        if (
            count(
                array_filter($currCrashes, fn($v) => str_contains($v, '.log'))
            ) > count($this->LastCrashes)
        ) {
            $file = @fopen(
                str_replace('plugins\HTNProtocol\src', 'crashdumps', __DIR__) .
                    "\\$crash",
                'r',
                true
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
                echo $crash;
                // var_dump($this->sock->sendData(new ServerCrash($crash), "all"));
            }
        }
        isset($this->db) ? $this->db->close() : 0;
        $this->sock->closeConnection();
    }
}
/**
 * @param array{'p': 12} $foo
 */
function foo($foo) {
    $foo[''];
}
