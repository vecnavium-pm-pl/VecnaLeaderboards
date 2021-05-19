<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Commands;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat as C;

class StatsCommand extends PluginCommand {
    public function __construct(Plugin $plugin) {
        parent::__construct("stats", $plugin);
        $this->setPermission("vecnaviumleaderboards.stats");
        $this->setDescription("Stats command!");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage("§cYou do not have permissions to run this command.");
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cPlease run the command in-game.");
            return true;
        }
        switch ($args[0] ?? "stats") {
            case "stats":
                $this->mainForm($sender);
        }
        return true;
    }
    public function MainForm(Player $player) {
        $form = new CustomForm(function (Player $sender, $data) {
            if ($data === null) {
                return true;
            }
            if(isset($data[1])) {
                $player = $this->getPlugin()->getServer()->getPlayerExact($data[1]);
                if ($player !== null) {
                    $data = $this->getPlugin()->getData($player->getName());
                    $name = $player->getName();
                } else {
                    $sender->sendMessage("§cThis player is either not online or does not exist.");
                    return true;
                }
            }
            $sender->sendMessage(C::RED . "[" . C::YELLOW . "Player" . C::YELLOW . "Statistics" . C::RED . "] \n" . C::RED . "=============\n" . C::WHITE . "+ Player: " . $name . "\n" . C::WHITE . "+ Kills: " . $data->getKills() . "\n" . C::WHITE . "* Killstreak: " . $data->getStreak() . "\n" . C::WHITE . "+ Deaths: " . $data->getDeaths() .  "\n" .  C::RED . "=============");
            return true;
        });
        $form->setTitle('§cVecna§eLeaderboards stats');
        $form->addLabel('Enter the in-game name of the player you wish to see stats for and then press submit.');
        $form->addInput('Username', 'Enter the username here');
        $player->sendForm($form);
    }
}