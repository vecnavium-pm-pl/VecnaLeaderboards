<?php

/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;
use Vecnavium\VecnaLeaderboards\Util\PluginUtils;

/**
 * Class LeaderboardManager
 * @property Player player
 * @package Vecnavium\VecnaLeaderboards\Leaderboard
 */
class LeaderboardManager
{
	/** @var Main */
	private $plugin;
	/** @var Leaderboard[] */
	private $leaderboards = [];

    /**
	 * ScoreboardManager constructor.
	 */
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		$interval = $plugin->getYamlProvider()->getUpdateInterval();
		$this->loadLeaderboards();
		$plugin->getScheduler()->scheduleDelayedRepeatingTask(new UpdateLeaderboardsTask($this, $plugin), $interval * 20, $interval * 20);		}

	public function loadLeaderboards(): void
	{
		$config = new Config($this->plugin->getDataFolder() . 'leaderboards.json', Config::JSON);
		$leaderboards = $config->getAll();
		foreach ($leaderboards as $position => $leaderboardType) {
			$this->registerLeaderboard(Entity::$entityCount++, $leaderboardType, PluginUtils::positionFromString($position));
		}
	}

	public function registerLeaderboard(int $id, string $type, Position $position): void
	{
		$this->leaderboards[$id] = $leaderboard = new Leaderboard($id, $type, $position);
		$leaderboard->spawn();
	}

	/**
	 * @param int $id
	 */
	public function unregisterLeaderboard(int $id): void
	{
		if (isset($this->leaderboards[$id])) unset($this->leaderboards[$id]);
	}

	public function saveLeaderboards(): void
	{
		$config = new Config($this->plugin->getDataFolder() . 'leaderboards.json', Config::JSON);
		foreach ($this->leaderboards as $leaderboard) {
			$config->set(PluginUtils::positionToString($leaderboard->getPosition()), $leaderboard->getType());
		}
		$config->save();
	}

	/**
	 * @param Player $player
	 * @param Level $target
	 * @param Level|null $origin
	 */
	public function handleLeaderboardSpawning(Player $player, Level $target, ?Level $origin = null)
	{
		foreach ($this->leaderboards as $leaderboard) {
			if ($leaderboard->getPosition()->getLevel() === $origin) {
				$leaderboard->despawn($player);
			}
			if ($leaderboard->getPosition()->getLevel() === $target) {
				$leaderboard->spawn($player);
			}
		}
	}

	/**
	 * @param Player $player
	 * @return Leaderboard|null
	 */
	public function getNearLeaderboard(Player $player): ?Leaderboard
	{
		foreach ($this->leaderboards as $leaderboard) {
			if ($leaderboard->getPosition()->distance($player->asVector3()) < 3) {
				return $leaderboard;
			}
		}
		return null;
	}

	/**
	 * @return Leaderboard[]
	 */
	public function getLeaderboards(): array
	{
		return $this->leaderboards;
	}

	/**
	 * @return Main
	 */
	public function getPlugin(): Main
	{
		return $this->plugin;
	}
}