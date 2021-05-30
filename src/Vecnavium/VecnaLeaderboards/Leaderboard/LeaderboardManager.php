<?php

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;
use Vecnavium\VecnaLeaderboards\Util\PluginUtils;

class LeaderboardManager
{
	private Main $plugin;
	/** @var Leaderboard[] */
	private array $leaderboards = [];

	/**
	 * ScoreboardManager constructor.
	 */
	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
		$interval = $plugin->getYamlProvider()->getUpdateInterval();
		$this->loadLeaderboards();
		$plugin->getScheduler()->scheduleDelayedRepeatingTask(new UpdateLeaderboardsTask($this), $interval * 20, $interval * 20);
	}

	public function loadLeaderboards()
	{
		$config = new Config($this->plugin->getDataFolder() . 'leaderboards.yml', Config::YAML);
		$leaderboards = $config->getAll();
		foreach ($leaderboards as $position => $leaderboardType) {
			$this->registerLeaderboard(Entity::$entityCount++, $leaderboardType, PluginUtils::positionFromString($position));
		}
	}

	public function registerLeaderboard(int $id, string $type, Position $position)
	{
		$this->leaderboards[$id] = $leaderboard = new Leaderboard($id, $type, $position);
		$leaderboard->spawn();
	}

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
	 * @param Level $level
	 */
	public function handleLeaderboardSpawning(Player $player, Level $level)
	{
		foreach ($this->leaderboards as $leaderboard) {
			$leaderboard->despawn($player);
		}
		foreach ($this->leaderboards as $leaderboard) {
			if ($leaderboard->getPosition()->getLevel() === $level) {
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