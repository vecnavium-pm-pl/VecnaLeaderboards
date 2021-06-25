<?php
declare(strict_types=1);

namespace Vecnavium\VecxLeaderboards\Leaderboard;

use pocketmine\scheduler\Task;

/**
 * Class UpdateLeaderboardsTask
 * @package Vecnavium\VecxLeaderboards\Leaderboard
 */
class UpdateLeaderboardsTask extends Task
{
	/** @var LeaderboardManager */
	private $manager;

	/**
	 * UpdateLeaderboardsTask constructor.
	 * @param LeaderboardManager $manager
	 */
	public function __construct(LeaderboardManager $manager)
	{
		$this->manager = $manager;
	}

	/**
	 * @param int $currentTick
	 */
	public function onRun(int $currentTick): void
	{
		foreach ($this->manager->getLeaderboards() as $leaderboard) {
			$leaderboard->tick($currentTick);
		}
	}
}


