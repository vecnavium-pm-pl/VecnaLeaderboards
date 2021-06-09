<?php
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
		parent::__construct("leaderboard", "Leaderboards Command", "/lb help", ["leaderboard", "lb"]);
		$this->setPermission("vecnaleaderboards.lb");
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

		if (!isset($args[0])) {
			$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. Example..\n/lb kills\b/lb streaks\n/lb delete");
			return false;
		}
		switch ($args[0]) {
			case Main::LEADERBOARD_TYPE_KILLS:
            case Main::LEADERBOARD_TYPE_STREAKS:
				$this->plugin->getLeaderboardManager()->registerLeaderboard(Entity::nextRuntimeId(), $args[0], $sender->getLocation()->asPosition());
				$sender->sendMessage(C::GRAY . "[" . C::WHITE . "VecnaLeaderboards" . C::WHITE . "" . C::GRAY . "] \n" . C::GREEN . $args[0] . " Leaderboard has been created!");
				break;
			case "del":
			case "remove":
			case "delete":
				$nearLeaderboard = $this->plugin->getLeaderboardManager()->getNearLeaderboard($sender);
				if ($nearLeaderboard === null) {
					$sender->sendMessage(C::RED . "ERROR: Leaderboard not found. \nBe sure to be close to the Leaderboard to delete it!");
					break;
				}
				$this->plugin->getLeaderboardManager()->unregisterLeaderboard($nearLeaderboard->getId());
				$sender->sendMessage(C::GOLD . "Success! Leaderboard has removed.");
				break;
			default:
				$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. Example..\n/lb kills\n/lb streaks\n/lb delete");
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
