<?php
namespace HTNProtocol\Models\Responses;

use HTNProtocol\Exceptions\InvalidSetException;

class PlayerInfo {
    public const MEMBER = 'member';
    public const DEFENDER = 'defender';
    public const GUARDIAN = 'guardian';
    public const PRESERVER = 'preserver';
    public const CHALLENGER = 'challenger';
    public const BANNED = 'banned';
    public const TEMP_BANNED = 'temp_banned';
    public const ONLINE = 'online';
    public const OFFLINE = 'offline';
    public string $rank;
    public bool $is_staff = false;
    /**
     * @var self::ONLINE|self::BANNED|self::TEMP_BANNED|self::OFFLINE $status
     */
    public string $status;
    public string $island_id;
    public int $play_time;
    public int $money = 0;
    /**
     * @var array{current_place: int, kills: int} $kill_stat
     */
    public array $kill_stat;
    public int $bank_money = 0;
    public string $xuid;
    /**
     * @var array{
     *  days: int,
     *  hours: int,
     *  minutes: int,
     * } $ban_expiration Should only be used when $status is self::TEMP_BANNED
     */
    public string $ban_expiration;
    /**
     * @var string[] $friends
     */
    public ?array $friends;
    public int $votes = 0;
    public function __set($name, $value) {
        if ($name === 'rank') {
            if (
                !(
                    $value === 'member' ||
                    $value === 'defender' ||
                    $value === 'guardian' ||
                    $value === 'preserver' ||
                    $value === 'challenger'
                )
            ) {
                throw new InvalidSetException(
                    $name,
                    $value,
                    'Should be a valid rank'
                );
            }
        }
        if ($name === 'friends') {
            foreach ($value as $key => $value) {
                if (!is_string($value)) {
                    throw new InvalidSetException(
                        $name,
                        $value,
                        'Should be a string'
                    );
                }
            }
        }
        if ($name === 'status') {
            if (
                !(
                    $value === self::BANNED ||
                    $value === self::TEMP_BANNED ||
                    $value === self::ONLINE ||
                    $value === self::OFFLINE
                )
            ) {
                throw new InvalidSetException(
                    $name,
                    $value,
                    'It should be a valid status'
                );
            }
        }
    }
}
