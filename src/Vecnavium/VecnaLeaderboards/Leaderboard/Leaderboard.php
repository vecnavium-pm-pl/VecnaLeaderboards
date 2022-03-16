<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\world\Position;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use Vecnavium\VecnaLeaderboards\Main;
use Vecnavium\VecnaLeaderboards\Util\CustomFloatingText;
use Vecnavium\VecnaLeaderboards\Util\PluginUtils;

/**
 * Class Leaderboard
 * @package Vecnavium\VecnaLeaderboards\Leaderboard
 */
class Leaderboard
{
	/** @var int */
	private int $id;
	/** @var string */
	private string $type;
	/** @var Position */
	private Position $position;
	/** @var CustomFloatingText|null */
	private CustomFloatingText|null $text = null;


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

	public function tick(): void
	{
		switch ($this->type) {
			case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_DEATHS:
            case Main::LEADERBOARD_TYPE_LEVELS:
				$title = PluginUtils::colorize($this->getPlugin()->getConfig()->get($this->type));
				foreach ($this->position->getWorld()->getPlayers() as $player) {
					$this->text->update(
						TextFormat::GOLD . $title . "\n" .
						$this->getPlugin()->getJsonProvider()->getRankings($this->type), $player
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
			$this->getPlugin()->getJsonProvider()->getRankings($this->type);
		$this->text = new CustomFloatingText($text, $this->position, $this->id);
		if ($player === null) {
			foreach ($this->position->getWorld()->getPlayers() as $player) {
				if (!$player->isOnline()) {
					continue;
				}
				$this->text->spawn($player);
			}
		} else {
			$this->text->spawn($player);
		}
	}

	public function despawn(?Player $player = null): void
	{
		if ($this->text === null || $this->position === null || !$this->position->isValid()) {
			return;
		}
		
		if ($player === null) {
			foreach ($this->position->getWorld()->getPlayers() as $player) {
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
