<?php

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Vecnavium\VecnaLeaderboards\Main;
use Vecnavium\VecnaLeaderboards\Util\CustomFloatingText;
use Vecnavium\VecnaLeaderboards\Util\PluginUtils;

class Leaderboard
{
	private int $id;
	private string $type;
	private Position $position;
	private ?CustomFloatingText $text = null;


	/**
	 * Scoreboard constructor.
	 * @param int $id
	 * @param string $type
	 * @param Position $position
	 */
	public function __construct(int $id, string $type, Position $position)
	{
		$this->id = $id;
		$this->type = $type;
		$this->position = $position;
	}

	/**
	 * @param int $currentTick
	 */
	public function tick(int $currentTick): void
	{
		switch ($this->type) {
			case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_STREAKS:
            case Main::LEADERBOARD_TYPE_LEVELS:
				$title = PluginUtils::colorize($this->getPlugin()->getConfig()->get($this->type));
				foreach ($this->position->getLevel()->getPlayers() as $player) {
					$this->text->update(
						TextFormat::GOLD . $title . "\n" .
						$this->getPlugin()->getYamlProvider()->getRankings($this->type), $player
					);
				}
				break;
		}
	}

	/**
	 * @param Player|null $player
	 */
	public function spawn(?Player $player = null): void
	{
		$title = PluginUtils::colorize($this->getPlugin()->getConfig()->get($this->type));
		$text = TextFormat::WHITE . "================\n" .
			$title . "\n" .
			$this->getPlugin()->getYamlProvider()->getRankings($this->type);
		$this->text = new CustomFloatingText($text, $this->position, $this->id);
		if ($player === null) {
			foreach ($this->position->getLevel()->getPlayers() as $player) {
				if (!$player->isOnline()) {
					continue;
				}
				$this->text->spawn($player);
			}
		} else {
			$this->text->spawn($player);
		}
	}

	public function despawn(?Player $player = null)
	{
		if ($this->text === null) {
			return;
		}
		if ($this->position->getLevel() === null) {
			return;
		}
		if ($player === null) {
			foreach ($this->position->getLevel()->getPlayers() as $player) {
				$this->text->remove($player);
			}
		} else {
			$this->text->remove($player);
		}
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return Position
	 */
	public function getPosition(): Position
	{
		return $this->position;
	}

	/**
	 * @return null|CustomFloatingText
	 */
	public function getText(): ?CustomFloatingText
	{
		return $this->text;
	}

	/**
	 * @return Main
	 */
	private function getPlugin(): Main
	{
		return Main::getInstance();
	}

	public function __destruct()
	{
		$this->despawn();
	}

}
