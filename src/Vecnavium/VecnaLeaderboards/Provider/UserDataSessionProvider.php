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
	private $player;
	/** @var Config */
	private $config;
	/** @var int */
	private $currentStreak = 0;

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
		$this->currentStreak++;
		if ($this->currentStreak > 5 && $this->currentStreak > $this->getStreak()) {
			$this->setStreak($this->currentStreak);
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
		$this->currentStreak = 0;
	}

	/**
	 * @return int
	 */
	public function getStreak(): int
	{
		return (int)$this->config->get('killstreak', 0);
	}

	/**
	 * @param int $streak
	 */
	public function setStreak(int $streak): void
	{
		$this->config->set('killstreak', $streak);
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
	 * @return int
	 */
	public function getCurrentStreak(): int
	{
		return $this->currentStreak;
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
