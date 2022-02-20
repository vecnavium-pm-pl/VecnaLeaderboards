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
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\plugin\{Plugin, PluginOwned, PluginOwnedTrait};
use Vecnavium\VecnaLeaderboards\Main;

/**
 * Class LeaderboardCommand
 * @package Vecnavium\VecnaLeaderboards\Commands
 */
class LeaderboardCommand extends VanillaCommand implements PluginOwned
{
	use PluginOwnedTrait;

    /** @var Main */
    private $plugin;

	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct(Main $plugin)
	{
		parent::__construct("lbmanage", "Create or delete leaderboards");
		$this->setPermission("vecnaleaderboards.manage");
        $this->owningPlugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool
	 */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->owningPlugin->getMessage("error.runingame"));
            return false;
        }

        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagenoperm"));
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatelbtitle"));
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatekills"));
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatestreaks"));
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatedeaths"));
            $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatelevels"));

            return false;
        }
        switch ($args[0]) {
            case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_DEATHS:
            case Main::LEADERBOARD_TYPE_STREAKS:
            case Main::LEADERBOARD_TYPE_LEVELS:
                $this->owningPlugin->getLeaderboardManager()->registerLeaderboard(Entity::nextRuntimeId(), $args[0], $sender->getLocation()->asPosition());
            $sender->sendMessage($this->owningPlugin->getMessage("success.lbcreatesuccess"));
                break;
            case "del":
            case "remove":
            case "delete":
                $nearLeaderboard = $this->owningPlugin->getLeaderboardManager()->getNearLeaderboard($sender);
                if ($nearLeaderboard === null) {
                    $sender->sendMessage($this->owningPlugin->getMessage("error.lbnotfound"));
                    break;
                }
                $this->owningPlugin->getLeaderboardManager()->unregisterLeaderboard($nearLeaderboard->getId());
            $sender->sendMessage($this->owningPlugin->getMessage("sucess.lbdel"));
                break;
            default:
                $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatelbtitle"));
                $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatekills"));
                $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatestreaks"));
                $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatedeaths"));
                $sender->sendMessage($this->owningPlugin->getMessage("error.lbmanagestatelevels"));
                return false;
        }
        return true;
    }
}
