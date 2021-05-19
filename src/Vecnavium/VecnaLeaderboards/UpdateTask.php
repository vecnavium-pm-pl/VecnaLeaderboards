<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards;

use pocketmine\scheduler\Task;

class UpdateTask extends Task {

private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun($currentTick) : void {
        $this->plugin->updateTexts();
    }
}


