<?php
declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Main;

/**
 * Class LeaderboardCommand
 * @package Vecnavium\VecnaLeaderboards\Commands
 */
class LeaderboardCommand extends Command implements PluginIdentifiableCommand
{
	/** @var Main */
	private $plugin;

	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct(Main $plugin)
	{
		parent::__construct("leaderboard", "VecxLeaderboards Command", "/lb help", ["leaderboard", "lb"]);
		$this->setPermission("vecnaLeaderboards.lb");
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
			$sender->sendMessage(C::RED . "ERROR: Please run this command ingame");
			return false;
		}
		
		if (!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(C::RED . "You do not have permission to use this command!");
			return false;
		}

		if (!isset($args[0])) {
            $sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. For example..");
            $sender->sendMessage(C::WHITE . "Top Kills LeaderBoard: /lb kills");
            $sender->sendMessage(C::WHITE . "Top Kill Streaks Leaderboard: /lb streaks");
            $sender->sendMessage(C::WHITE . "Top Deaths Leaderboard: /lb deaths");
            $sender->sendMessage(C::WHITE . "Top Levels Leaderboard: /lb levels");
            $sender->sendMessage(C::WHITE . "Top KDR Leaderboard: /lb kdr");
            $sender->sendMessage(C::WHITE . "Deleting a Leaderboard: /lb delete");
            return false;
		}
		switch ($args[0]) {
			case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_STREAKS:
			case Main::LEADERBOARD_TYPE_DEATHS:
			case Main::LEADERBOARD_TYPE_LEVELS:
            case Main::LEADERBOARD_TYPE_KDR:
				$this->plugin->getLeaderboardManager()->registerLeaderboard(Entity::$entityCount++, $args[0], $sender->asPosition());
				$sender->sendMessage(C::GRAY . "[" . C::WHITE . "VecnaLeaderboards" . C::WHITE . "" . C::GRAY . "] \n" . C::GREEN . $args[0] . " Leaderboard has been created!");
				break;
			case "del":
			case "remove":
			case "delete":
				$nearLeaderboard = $this->plugin->getLeaderboardManager()->getNearLeaderboard($sender);
				if ($nearLeaderboard === null) {
                    $sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. For example");
                    $sender->sendMessage(C::WHITE . "Top Kills LeaderBoard: /lb kills");
                    $sender->sendMessage(C::WHITE . "Top Kill Streaks Leaderboard: /lb streaks");
                    $sender->sendMessage(C::WHITE . "Top Deaths Leaderboard: /lb deaths");
                    $sender->sendMessage(C::WHITE . "Top Levels Leaderboard: /lb levels");
                    $sender->sendMessage(C::WHITE . "Top KDR Leaderboard: /lb kdr");
                    $sender->sendMessage(C::WHITE . "Deleting a Leaderboard: /lb delete");
                    break;
				}
				$this->plugin->getLeaderboardManager()->unregisterLeaderboard($nearLeaderboard->getId());
				$sender->sendMessage(C::GOLD . "Success! Leaderboard has removed.");
				break;
			default:
				$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. For example.");
                $sender->sendMessage(C::WHITE . "Top Kills LeaderBoard: /lb kills");
                $sender->sendMessage(C::WHITE . "Top Kill Streaks Leaderboard: /lb streaks");
                $sender->sendMessage(C::WHITE . "Top Deaths Leaderboard: /lb deaths");
                $sender->sendMessage(C::WHITE . "Top Levels Leaderboard: /lb levels");
                $sender->sendMessage(C::WHITE . "Top KDR Leaderboard: /lb kdr");
                $sender->sendMessage(C::WHITE . "Deleting a Leaderboard: /lb delete");

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
