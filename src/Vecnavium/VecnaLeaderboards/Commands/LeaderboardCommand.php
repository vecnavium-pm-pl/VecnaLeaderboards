<?php

namespace Vecnavium\VecnaLeaderboards\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Main;

class LeaderboardCommand extends Command implements PluginIdentifiableCommand
{
	private Main $plugin;

	/**
	 * LeaderboardCommand constructor.
	 */
	public function __construct(Main $plugin)
	{
		parent::__construct("leaderboard", "leaderboard command help", "/ldb help", ["leaderboard", "ldb"]);
		$this->plugin = $plugin;
		$this->setPermission("vecnaviumleaderboards.leaderboard");
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
		if (!isset($args[0]) || !Main::isValidLeaderboard($args[0])) {
			$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. Example..\n/lb kills\n/lb delete");
			return false;
		}
		switch ($args[0]) {
			case Main::LEADERBOARD_TYPE_KILLS:
				$this->plugin->getLeaderboardManager()->registerLeaderboard(Entity::$entityCount++, $args[0], $sender->asPosition());
				$sender->sendMessage(C::GRAY . "[" . C::WHITE . "VecnaLeaderboards" . C::WHITE . "" . C::GRAY . "] \n" . C::GREEN . $args[0] . "Leaderboard has been created!");
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
		}
		return true;
	}


	public function getPlugin(): Plugin
	{
		return $this->plugin;
	}

}