<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Commands;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Main;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;

class VersionCommand extends VanillaCommand
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct("lbversion", "VencaLeaderboards version command", "/lbversion", ["lbversion"]);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!isset($args[0])) {
            $sender->sendMessage(C::GREEN . "VecnaLeaderboards Plugin Information");
            $sender->sendMessage("Author: @Vecnavium");
            $sender->sendMessage("Source Code: https://github.com/Vecnavium/VecnaLeaderboards");
            $sender->sendMessage("Plugin Version: v2.0.1");
            $sender->sendMessage("Dev Build: True");
        }
        return true;
    }

}