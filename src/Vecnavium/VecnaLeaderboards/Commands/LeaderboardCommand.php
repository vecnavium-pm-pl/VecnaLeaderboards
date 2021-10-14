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
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Main;

/**
 * Class LeaderboardCommand
 * @package Vecnavium\VecnaLeaderboards\Commands
 */
class LeaderboardCommand extends VanillaCommand
{
	/** @var Main */
	private $plugin;

	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct(Main $plugin)
	{
		parent::__construct("lbmanage", "Create or delete leaderboards");
		$this->setPermission("vecnaleaderboards.manage");
		$this->plugin = $plugin;
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
            $sender->sendMessage(C::RED . "Error: Please run this command ingame");
            return false;
        }

        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(C::RED . "You do not have permission to use this command!");
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage(C::RED . "Error: Please state what type of Leaderboard you want. For example");
            $sender->sendMessage(C::WHITE . "Top Kills LeaderBoard: /lbmanage kills");
            $sender->sendMessage(C::WHITE . "Top Kill Streaks Leaderboard: /lbmanage streaks");
            $sender->sendMessage(C::WHITE . "Top Deaths Leaderboard: /lbmanage deaths");
            $sender->sendMessage(C::WHITE . "Top Levels Leaderboard: /lbmanage levels");
            return false;
        }
        switch ($args[0]) {
            case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_DEATHS:
            case Main::LEADERBOARD_TYPE_STREAKS:
            case Main::LEADERBOARD_TYPE_LEVELS:
                $this->plugin->getLeaderboardManager()->registerLeaderboard(Entity::nextRuntimeId(), $args[0], $sender->getLocation()->asPosition());
                $sender->sendMessage(C::GRAY . "[" . C::WHITE . "VecnaLeaderboards" . C::WHITE . "" . C::GRAY . "] \n" . C::GREEN . $args[0] . " Leaderboard has been created!");
                break;
            case "del":
            case "remove":
            case "delete":
                $nearLeaderboard = $this->plugin->getLeaderboardManager()->getNearLeaderboard($sender);
                if ($nearLeaderboard === null) {
                    $sender->sendMessage(C::RED . "Error: Leaderboard not found.");
                    $sender->sendMessage(C::WHITE ."Be sure to be close to the Leaderboard to delete it!");
                    break;
                }
                $this->plugin->getLeaderboardManager()->unregisterLeaderboard($nearLeaderboard->getId());
                $sender->sendMessage(C::GREEN . "Success! Leaderboard has removed.");
                break;
            default:
                $sender->sendMessage(C::RED . "Error: Please state what type of Leaderboard you want. For example");
                $sender->sendMessage(C::WHITE . "Top Kills LeaderBoard: /lbmanage kills");
                $sender->sendMessage(C::WHITE . "Top Kill Streaks Leaderboard: /lbmanage streaks");
                $sender->sendMessage(C::WHITE . "Top Deaths Leaderboard: /lbmanage deaths");
                $sender->sendMessage(C::WHITE . "Top Levels Leaderboard: /lbmanage levels");
                return false;
        }
        return true;
    }


    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }

}