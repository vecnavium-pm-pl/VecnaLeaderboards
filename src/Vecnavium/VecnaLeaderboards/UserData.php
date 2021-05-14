<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards;

use Vecnavium\VecnaLeaderboards\Main;

class UserData {

    private $plugin;
    private $player = null;
    private $kills = 0;
    private $killStreak = 0;
    private $deaths = 0;
    private $data = null;

    public function __construct(Main $plugin, $player){
        $this->plugin = $plugin;
        $this->player = $player;
        $path = $this->getPath();
        if(is_file($path)) {
            $data = yaml_parse_file($path);
            $this->data = $data;
            $this->kills = $data["kills"];
            $this->deaths = $data["deaths"];
            $this->killStreak = $data["killstreak"] ?? 0;
        } else {
            return;
        }
    }

    public function getStats() {
        return $this->data;
    }

    public function getName() {
        return $this->player;
    }

    public function getKills() {
        return $this->kills;
    }

    public function getStreak() {
        return $this->killStreak;
    }

    public function getDeaths() {
        return $this->deaths;
    }

    public function addKill() {
        $this->kills++;
        $this->killStreak++;
        $this->save();
    }

    public function addDeath() {
        $this->deaths++;
        $this->killStreak = 0;
        $this->save();
    }


    public function getPath() {
        return $this->plugin->getDataFolder() . "data/" . strtolower($this->player) . ".yml";
    }

    public function save() {
        yaml_emit_file($this->getPath(), ["name" => $this->player, "kills" => $this->kills, "killstreak" => $this->killStreak, $this->kills, "deaths" => $this->deaths]);
    }

}
