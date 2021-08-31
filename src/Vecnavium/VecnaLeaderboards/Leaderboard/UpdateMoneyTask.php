<?php

/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards\Leaderboard;

use onebone\economyapi\EconomyAPI;
use onebone\economyapi\event\money\MoneyChangedEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Main;

class UpdateMoneyTask implements Listener {
    public function onMoneyUpdate(MoneyChangedEvent $event) {
        $name = $event->getUsername();
        $config = new Config(Main::getInstance()->getDataFolder() . "data/$name.json");
        if($config->get("money") !== EconomyAPI::getInstance()->myMoney($name)) {
            $config->set("money", EconomyAPI::getInstance()->myMoney($name));
            $config->save();
        } else {
            if($config->get("money") === null) {
                $config->set("money", EconomyAPI::getInstance()->myMoney($name));
                $config->save();
            }
        }
    }
}