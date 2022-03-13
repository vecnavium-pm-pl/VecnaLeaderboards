<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\entity\Entity;
use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;
use Vecnavium\VecnaLeaderboards\Main;
use Vecnavium\VecnaLeaderboards\Util\PluginUtils;

/**
 * Class LeaderboardManager
 * @package Vecnavium\VecnaLeaderboards\Leaderboard
 */
class LeaderboardManager
{
	/** @var Main */
	private Main $plugin;
	/** @var Leaderboard[] */
	private array $leaderboards = [];

	/**
	 * ScoreboardManager constructor.
	 */
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		$interval = $plugin->getJsonProvider()->getUpdateInterval();
		$this->loadLeaderboards();
		$plugin->getScheduler()->scheduleDelayedRepeatingTask(new UpdateLeaderboardsTask($this), $interval * 20, $interval * 20);
	}

	public function loadLeaderboards(): void
	{
		$config = new Config($this->plugin->getDataFolder() . 'leaderboards.yml', Config::YAML);
		$leaderboards = $config->getAll();
		foreach ($leaderboards as $position => $leaderboardType) {
			$this->registerLeaderboard(Entity::nextRuntimeId(), $leaderboardType, PluginUtils::positionFromString($position));
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
		$config = new Config($this->plugin->getDataFolder() . 'leaderboards.yml', Config::YAML);
		foreach ($this->leaderboards as $leaderboard) {
			$config->set(PluginUtils::positionToString($leaderboard->getPosition()), $leaderboard->getType());
		}
		$config->save();
	}

	/**
	 * @param Player $player
	 * @param World $target
	 * @param World|null $origin
	 */
	public function handleLeaderboardSpawning(Player $player, World $target, ?World $origin = null)
	{
		foreach ($this->leaderboards as $leaderboard) {
			if ($leaderboard->getPosition()->getWorld() === $origin) {
				$leaderboard->despawn($player);
			}
			if ($leaderboard->getPosition()->getWorld() === $target) {
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
			if ($leaderboard->getPosition()->distance($player->getLocation()->asVector3()) < 3) {
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