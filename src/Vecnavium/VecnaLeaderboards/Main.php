<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use Vecnavium\VecnaLeaderboards\Commands\StatsCommand;
use Vecnavium\VecnaLeaderboards\Util\CustomFloatingText;

class Main extends PluginBase
{

	private $cfg;
	private $texts;
	private $UserData = [];
	/** @var CustomFloatingText[] */
	private $particles = [];

	public function onEnable()
	{
		$this->saveDefaultConfig();
		$this->reloadConfig();
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "data/");
		$this->cfg = $this->getConfig();
		$this->texts = new Config($this->getDataFolder() . "leaderboards.yml", Config::YAML);
		$listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		$this->getServer()->getCommandMap()->register("stats", new StatsCommand($this));
		$interval = $this->cfg->get("texts")["leaderboard-timer"] ?? 60;
		$this->getScheduler()->scheduleDelayedRepeatingTask(new UpdateTask($this), $interval * 20, $interval * 20);
	}

	public function joinText(string $name)
	{
		foreach ($this->texts->getAll() as $loc => $type) {
			$pos = explode("_", $loc);
			if (isset($pos[1])) {
				$v3 = new Vector3(round($pos[0], 2), round($pos[1], 2), round($pos[2], 2));
				$this->createText($v3, $type);
			}
		}
	}

	public function createText(Vector3 $location, string $type = "levels")
	{
		$typetitle = $this->colorize($this->getConfig()->get("texts")[$type]);
		$id = implode("_", [$location->getX(), $location->getY(), $location->getZ()]);
		$level = $this->getServer()->getLevelByName($this->cfg->get("texts")["world"]);
		$particle = new CustomFloatingText(C::WHITE . "================\n" . $typetitle . "\n" . $this->getRankings($type), Position::fromObject($location, $level));
		$particle->spawn();
		$this->particles[$id] = $particle;
	}

	public function updateTexts()
	{
		foreach ($this->particles as $id => $text) {
			$type = $this->texts->get($id);
			$typetitle = $this->colorize($this->getConfig()->get("texts")[$type]);
			$text->update(C::GOLD . $typetitle . "\n" . $this->getRankings($type));
		}
	}

	public function addKill(Player $player)
	{
		$data = $this->getData($player->getName());
		$data->addKill();
	}

	public function handleStreak(Player $player, Player $v)
	{
		$killer = $this->getData($player->getName());
		$loser = $this->getData($v->getName());
		$oldStreak = $loser->getStreak();
		if ($oldStreak >= 5) {
			$v->sendMessage(C::GRAY . "" . C::DARK_GREEN . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . "Your " . $oldStreak . " killstreak was ended by " . $player->getName() . "!");
			$player->sendMessage(C::GRAY . "" . C::DARK_RED . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . "You have ended " . $v->getName() . "'s " . $oldStreak . " killstreak!");
		}
		$newStreak = $killer->getStreak();
		if (is_int($newStreak / 5)) {
			$this->getServer()->broadcastMessage(C::GRAY . "" . C::DARK_RED . "KillStreak alert:" . C::GRAY . "> " . C::WHITE . $player->getName() . " is on a " . $newStreak . " killstreak. Go kill them to end their streak! ");
		}
	}

	public function addDeath(Player $player)
	{
		$this->getData($player->getName())->addDeath();
		return;
	}

	public function getData($name)
	{
		return new UserData($this, $name);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		if (strtolower($command->getName()) == "leaderboard") {
			if ($sender instanceof Player) {
				if (isset($args[0])) {
					if (in_array($args[0], ["kills"])) {
						$v3 = implode("_", [round($sender->getX(), 2), round($sender->getY(), 2) + 1.7, round($sender->getZ(), 2)]);
						$this->texts->set($v3, $args[0]);
						$this->texts->save();
						$this->createText(new Vector3(round($sender->getX(), 2), round($sender->getY(), 2) + 1.7, round($sender->getZ(), 2)), $args[0]);
						$sender->sendMessage(C::GRAY . "[" . C::WHITE . "VecnaLeaderboards" . C::WHITE . "" . C::GRAY . "] \n" . C::GREEN . $args[0] . "Leaderboard has been created!");
						return true;
					} elseif (in_array($args[0], ["del", "remove", "delete"])) {
						$text = $this->isNearText($sender);
						if (isset($this->particles[$text])) {
							if ($this->particles[$text] instanceof CustomFloatingText) {
								$this->particles[$text]->remove();
								$this->texts->remove($text);
								$this->texts->save();
								if (isset($this->particles[$text])) {
									unset($this->particles[$text]);
								}

								$sender->sendMessage(C::GOLD . "Success! Leaderboard has removed.");
								return true;
							} else {
								$sender->sendMessage(C::RED . "ERROR: Leaderboard not found. \nBe sure to be close to the Leaderboard to delete it!");
								return true;
							}
						} else {
							$sender->sendMessage(C::RED . "ERROR: Leaderboard not found. \nBe sure to be close to the Leaderboard to delete it!");
							return true;
						}
					} else {
						$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. Example..\n/lb kills\n/lb delete");
						return true;
					}
				} else {
					$sender->sendMessage(C::RED . "ERROR: Please state what type of Leaderboard you want. Example..\n/lb kills\n/lb delete");
					return true;
				}
			} else {
				$sender->sendMessage(C::RED . "ERROR: Please run this command ingame");
				return true;
			}
		}
		return true;
	}

	public function isNearText($player)
	{
		foreach ($this->texts->getAll() as $loc => $type) {
			$v3 = explode("_", $loc);
			if (isset($v3[1])) {
				$text = new Vector3($v3[0], $v3[1], $v3[2]);
				if ($player->distance($text) <= 5 && $player->distance($text) > 0) {
					return $loc;
				}
			}
		}
		return false;
	}

	public function getRankings(string $type): string
	{
		$files = scandir($this->getDataFolder() . "data/");
		$stats = [];

		switch ($type) {
			case "kills":
				$string = "kills";
				break;
			default:
				break;
		}
		foreach ($files as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) == "yml") {
				$yaml = file_get_contents($this->getDataFolder() . "data/" . $file);
				$rawData = yaml_parse($yaml);
				if (isset($rawData[$type])) {
					$stats[$rawData["name"]] = $rawData[$type];
				}
			}
		}
		arsort($stats, SORT_NUMERIC);
		$finalRankings = "";
		$i = 1;
		foreach ($stats as $name => $number) {
			$finalRankings .= C::RED . $i . ".) " . $name . ": " . $number . "\n";
			if ($i > $this->getConfig()->get("texts")["leaderboard-top-length"]) {
				return $finalRankings;
			}
			if (count($stats) <= $i) {
				return $finalRankings;
			}
			$i++;
		}
		return "";
	}

	public function colorize(string $text)
	{
		$newText = str_replace("&", "§", $text);
		return $newText;
	}
}