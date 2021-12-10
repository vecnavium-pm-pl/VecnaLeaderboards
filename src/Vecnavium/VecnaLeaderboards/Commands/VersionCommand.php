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
use pocketmine\plugin\PluginOwnedTrait;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;

class VersionCommand extends VanillaCommand implements PluginOwned
{
    use PluginOwnedTrait;

    public function __construct(Main $plugin)
    {
        parent::__construct("lbversion", "VencaLeaderboards version command", "/lbversion", ["lbversion"]);
        $this->owningPlugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!isset($args[0])) {
            $sender->sendMessage($this->plugin->getMessage("versioncmd.versiontitle"));
            $sender->sendMessage($this->plugin->getMessage("versioncmd.author"));
            $sender->sendMessage($this->plugin->getMessage("versioncmd.sourcecode"));
            $sender->sendMessage($this->plugin->getMessage("versioncmd.pluginversion"));
            $sender->sendMessage($this->plugin->getMessage("versioncmd.devbuild"));
        }
        return true;
    }

}
