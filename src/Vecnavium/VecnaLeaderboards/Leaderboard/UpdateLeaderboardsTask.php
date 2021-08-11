<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\scheduler\Task;

/**
 * Class UpdateLeaderboardsTask
 * @package Vecnavium\VecnaLeaderboards\Leaderboard
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

	public function onRun(): void
	{
		foreach ($this->manager->getLeaderboards() as $leaderboard) {
			$leaderboard->tick();
		}
	}
}


