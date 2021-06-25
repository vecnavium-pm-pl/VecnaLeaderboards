<?php /** @noinspection PhpUnused */

declare(strict_types=1);

namespace Vecnavium\VecxLeaderboards;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use Vecnavium\VecxLeaderboards\Commands\LeaderboardCommand;
use Vecnavium\VecxLeaderboards\Commands\StatsCommand;
use Vecnavium\VecxLeaderboards\Leaderboard\LeaderboardManager;
use Vecnavium\VecxLeaderboards\Provider\UserDataSessionProvider;
use Vecnavium\VecxLeaderboards\Provider\YamlDataProvider;
use JackMD\UpdateNotifier\UpdateNotifier;

/**
 * Class Main
 * @package Vecnavium\VecxLeaderboards
 */
class Main extends PluginBase implements Listener
{

	public const LEADERBOARD_TYPE_KILLS = "kills";
	public const LEADERBOARD_TYPE_STREAKS = "streaks";
	public const LEADERBOARD_TYPE_DEATHS = "deaths";
	public const LEADERBOARD_TYPE_LEVELS = "levels";
    public const LEADERBOARD_TYPE_KDR = "kdr";

	/** @var Main */
	private static $instance;
	/** @var YamlDataProvider */
	private $yamlProvider;
	/** @var LeaderboardManager */
	private $leaderboardManager;
	/** @var UserDataSessionProvider[] */
	private $sessions = [];


	public function onEnable(): void
	{
		self::$instance = $this;
		$this->yamlProvider = new YamlDataProvider($this);
		$this->leaderboardManager = new LeaderboardManager($this);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register("VecxLeaderboards", new StatsCommand($this));
		$this->getServer()->getCommandMap()->register("VecxLeaderboards", new LeaderboardCommand($this));
	}
	public function onLoad()
    {
        UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
    }

	public function onDisable(): void
	{
		$this->leaderboardManager->saveLeaderboards();
	}

	/**
	 * @param string $option
	 * @return bool
	 */
	public static function isValidLeaderboard(string $option): bool
	{
		$options = [
			self::LEADERBOARD_TYPE_KILLS, self::LEADERBOARD_TYPE_STREAKS,
			self::LEADERBOARD_TYPE_DEATHS, self::LEADERBOARD_TYPE_LEVELS, self::LEADERBOARD_TYPE_KDR
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
	 * @return YamlDataProvider
	 */
	public function getYamlProvider(): YamlDataProvider
	{
		return $this->yamlProvider;
	}

	/**
	 * @return LeaderboardManager
	 */
	public function getLeaderboardManager(): LeaderboardManager
	{
		return $this->leaderboardManager;
	}
}
