<?php /** @noinspection PhpUnused */

/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);

namespace Vecnavium\VecnaLeaderboards;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Vecnavium\VecnaLeaderboards\Commands\LeaderboardCommand;
use Vecnavium\VecnaLeaderboards\Leaderboard\LeaderboardManager;
use Vecnavium\VecnaLeaderboards\Leaderboard\UpdateMoneyTask;
use Vecnavium\VecnaLeaderboards\Provider\UserDataSessionProvider;
use Vecnavium\VecnaLeaderboards\Provider\JsonDataProvider;
use Vecnavium\VecnaLeaderboards\CheckUpdateTask;

/**
 * Class Main
 * @package Vecnavium\VecnaLeaderboards
 */
class Main extends PluginBase implements Listener
{

    public const LEADERBOARD_TYPE_KILLS = "kills";
    public const LEADERBOARD_TYPE_STREAKS = "streaks";
    public const LEADERBOARD_TYPE_DEATHS = "deaths";
    public const LEADERBOARD_TYPE_LEVELS = "levels";
    public const LEADERBOARD_TYPE_KDR = "kdr";
    public const LEADERBOARD_TYPE_MONEY = "money";

    /** @var Main */
    private static $instance;
    /** @var JsonDataProvider */
    private $jsonProvider;
    /** @var LeaderboardManager */
    private $leaderboardManager;
    /** @var UserDataSessionProvider[] */
    private $sessions = [];

    public function onEnable(): void {
        self::$instance = $this;
        $this->jsonProvider = new JsonDataProvider($this);
        $this->leaderboardManager = new LeaderboardManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register("VecnaLeaderboards", new LeaderboardCommand($this));
        if($this->getConfig()->get('topmoney-leaderboard') == 'true'){
            if($this->getServer()->getPluginManager()->getPlugin('EconomyAPI') !== null){
                $this->getServer()->getPluginManager()->registerEvents(new UpdateMoneyTask(), $this);
            }
        }
        $this->checkUpdate();
        $this->checkEconomyPlugin();
    }

    public function checkUpdate(bool $isRetry = false): void {

        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask($this, $isRetry));
    }

    public function onDisable(): void
    {
        $this->leaderboardManager->saveLeaderboards();
    }

    public function checkEconomyPlugin() {
        if($this->getConfig()->get('topmoney-leaderboard') == 'true'){
            if($this->getServer()->getPluginManager()->getPlugin('EconomyAPI') === null){
                $this->getLogger()->warning('§cEconomyAPI Plugin not found. Please install EconomyAPI.');
                $this->getLogger()->warning('§cOtherwise please disable this feature by setting topmoney-leaderboard to false for the plugin to function.');
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        }
    }

    public static function isValidLeaderboard(string $option): bool
    {
        $options = [
            self::LEADERBOARD_TYPE_KILLS, self::LEADERBOARD_TYPE_STREAKS,
            self::LEADERBOARD_TYPE_DEATHS, self::LEADERBOARD_TYPE_LEVELS,
            self::LEADERBOARD_TYPE_KDR, self::LEADERBOARD_TYPE_MONEY
        ];
        return in_array($option, $options);
    }

    /**
     * @param Player $player
     * @return UserDataSessionProvider|null
     */
    public function getSessionFor(Player $player): ?UserDataSessionProvider
    {
        return $this->sessions[$player->getName()] ?? null;
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority NORMAL
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $this->sessions[$player->getName()] = new UserDataSessionProvider($player);
        $this->leaderboardManager->handleLeaderboardSpawning($player, $player->getLevel());
        $name = $event->getPlayer()->getName();
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

    /**
     * @param EntityLevelChangeEvent $event
     * @priority NORMAL
     */
    public function onEntityLevelChangeEvent(EntityLevelChangeEvent $event): void
    {
        $target = $event->getTarget();
        $origin = $event->getOrigin();
        $player = $event->getEntity();
        if (!$player instanceof Player) {
            return;
        }
        $this->leaderboardManager->handleLeaderboardSpawning($player, $target, $origin);
    }

    /**
     * @param EntityDamageEvent $event
     * @priority NORMAL
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $victim = $event->getEntity();
        if (!$victim instanceof Player) {
            return;
        }
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            if (!$damager instanceof Player) {
                return;
            }
            if ($event->getFinalDamage() > $victim->getHealth()) {
                $damagerSession = $this->getSessionFor($damager);
                $victimSession = $this->getSessionFor($victim);
                $damagerSession->addKill();
                $victimSession->addDeath($damager);
            }
            return;
        }
        if ($event->getFinalDamage() > $victim->getHealth()) {
            $session = $this->getSessionFor($victim);
            $session->addDeath();
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority NORMAL
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        if (isset($this->sessions[$player->getName()])) unset($this->sessions[$player->getName()]);
    }

    /**
     * @return Main
     */
    public static function getInstance(): Main
    {
        return self::$instance;
    }

    /**
     * @return JsonDataProvider
     */
    public function getJsonProvider(): JsonDataProvider
    {
        return $this->jsonProvider;
    }

    /**
     * @return LeaderboardManager
     */
    public function getLeaderboardManager(): LeaderboardManager
    {
        return $this->leaderboardManager;
    }
}

