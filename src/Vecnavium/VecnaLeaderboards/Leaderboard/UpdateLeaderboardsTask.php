<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\scheduler\Task;

class UpdateLeaderboardsTask extends Task
{

	private LeaderboardManager $manager;

	public function __construct(LeaderboardManager $manager)
	{

		$this->manager = $manager;
	}

	public function onRun($currentTick): void
	{
		foreach ($this->manager->getLeaderboards() as $leaderboard) {
			$leaderboard->tick($currentTick);
		}
	}
}


