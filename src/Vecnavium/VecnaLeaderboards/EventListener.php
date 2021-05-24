<?php

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {

    private $plugin;


    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $e) {
        $this->plugin->joinText($e->getPlayer()->getName());
    }

    public function onEntityLevelChange(EntityLevelChangeEvent $event){
		$level = Server::getInstance()->getLevelByName($this->plugin->cfg->get("texts")["world"]);
		$player = $event->getEntity();
		if (!$player instanceof Player){
			return;
		}
		if ($event->getOrigin() === $level){
			foreach ($this->plugin->particles as $text) {
				$text->remove($player);
			}
		}
		if ($event->getTarget() === $level){
			foreach ($this->plugin->particles as $text) {
				$text->spawn($player);
			}
		}
	}

    public function onDeath(PlayerDeathEvent $e) {
        $v = $e->getPlayer();
        $cause = $e->getPlayer()->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent) {
            if($cause->getDamager() instanceof Player) {
                $this->plugin->addKill($cause->getDamager());
                $this->plugin->handleStreak($cause->getDamager(), $v);
            }
        }
        $this->plugin->addDeath($v);
    }

}
