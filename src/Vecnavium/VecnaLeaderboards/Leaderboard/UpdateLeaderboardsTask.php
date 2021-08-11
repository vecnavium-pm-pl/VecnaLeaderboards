<?php

/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use pocketmine\scheduler\Task;
use Vecnavium\VecnaLeaderboards\Main;

/**
 * Class UpdateLeaderboardsTask
 * @property string playerName
 * @property Main plugin
 * @package Vecnavium\VecnaLeaderboards\Leaderboard
 */
class UpdateLeaderboardsTask extends Task
{
	/** @var LeaderboardManager */
	private $manager;

    /**
     * UpdateLeaderboardsTask constructor.
     * @param LeaderboardManager $manager
     * @param Main $plugin
     */
	public function __construct(LeaderboardManager $manager, Main $plugin)
	{
        $this->plugin = $plugin;
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


