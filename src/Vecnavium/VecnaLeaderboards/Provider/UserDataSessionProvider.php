<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Provider;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;
use pocketmine\utils\TextFormat as C;

class UserDataSessionProvider
{
	/** @var Player */
	private Player $player;
	/** @var Config */
	private Config $config;
	/** @var int */

	/**
	 * UserDataSessionProvider constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
		$this->config = new Config(Main::getInstance()->getDataFolder() . "data/{$player->getName()}.json");
	}

	/**
	 * @return int
	 */
	public function getKills(): int
	{
		return (int)$this->config->get('kills', 0);
	}

    public function addKill(): void
    {
        $kills = $this->getKills() + 1;
        $this->config->set('kills', $kills);
        $this->config->save();
        $playerLevel = $this->getLevel();
        foreach ($this->getPlugin()->getJsonProvider()->getLevel() as $level => $data) {
            if ($kills == $data['kills'] && $playerLevel < $level) {
                $this->player->sendPopup(C::DARK_GREEN . "You have successfully Leveled up!");
                $this->levelUp();
                }
            }
        }
	/**
	 * @return int
	 */
	public function getDeaths(): int
	{
		return (int)$this->config->get('deaths', 0);
	}

	/**
	 * @param Player|null $assasin
	 */
	public function addDeath(?Player $assasin = null): void
	{
		$deaths = $this->getDeaths();
		$this->config->set('deaths', $deaths + 1);
		$this->config->save();
	}


	/**
	 * @return int
	 */
	public function getLevel(): int
	{
		return (int)$this->config->get('level', 0);
	}

	/**
	 * @param int $level
	 */
	public function setLevel(int $level): void
	{
		$this->config->set('level', $level);
		$this->config->save();
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player
	{
		return $this->player;
	}


	/**
	 * @return Main
	 */
	public function getPlugin(): Main
	{
		return Main::getInstance();
	}

	private function levelUp(): void
	{
		$level = $this->getLevel() + 1;
		$this->config->set('level', $level);
		$this->config->save();
	}

}
