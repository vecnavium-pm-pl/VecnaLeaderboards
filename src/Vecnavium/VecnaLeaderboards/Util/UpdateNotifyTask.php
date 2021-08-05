<?php

declare(strict_types=1);

/*
 *  _   _           _       _       _   _       _   _  __ _
 * | | | |         | |     | |     | \ | |     | | (_)/ _(_)
 * | | | |_ __   __| | __ _| |_ ___|  \| | ___ | |_ _| |_ _  ___ _ __
 * | | | | '_ \ / _` |/ _` | __/ _ \ . ` |/ _ \| __| |  _| |/ _ \ '__|
 * | |_| | |_) | (_| | (_| | ||  __/ |\  | (_) | |_| | | | |  __/ |
 *  \___/| .__/ \__,_|\__,_|\__\___\_| \_/\___/ \__|_|_| |_|\___|_|
 *       | |
 *       |_|
 *
 * UpdateNotifier, a updater virion for PocketMine-MP
 * Copyright (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * This software is distributed under "GNU General Public License v3.0".
 *
 * UpdateNotifier is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 * ------------------------------------------------------------------------
 */

namespace Vecnavium\VecnaLeaderboards\Util;

use Vecnavium\VecnaLeaderboards\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use function is_array;
use function json_decode;
use function version_compare;
use function vsprintf;

class UpdateNotifyTask extends AsyncTask{

	/** @var string */
	private const POGGIT_RELEASES_URL = "https://poggit.pmmp.io/releases.json?name=";

	/** @var string */
	private $pluginName;
	/** @var string */
	private $pluginVersion;

	public function __construct(string $pluginName, string $pluginVersion){
		$this->pluginName = $pluginName;
		$this->pluginVersion = $pluginVersion;
	}

	public function onRun() : void{
		$json = Internet::getURL(self::POGGIT_RELEASES_URL . $this->pluginName, 10, [], $err);
		$highestVersion = $this->pluginVersion;
		$artifactUrl = "";
		$api = "";
		if($json !== false){
			$releases = json_decode($json, true);
			if($releases === null || !is_array($releases) || !$releases){
				$this->setResult([null, null, null, $err ?? "Unable to resolve host: " . self::POGGIT_RELEASES_URL . $this->pluginName]);
				return;
			}
			foreach($releases as $release){
				if(version_compare($highestVersion, $release["version"], ">=")){
					continue;
				}
				$highestVersion = $release["version"];
				$artifactUrl = $release["artifact_url"];
				$api = $release["api"][0]["from"] . " - " . $release["api"][0]["to"];
			}
		}

		$this->setResult([$highestVersion, $artifactUrl, $api, $err]);
	}

	public function onCompletion(Server $server) : void{
		$plugin = Server::getInstance()->getPluginManager()->getPlugin($this->pluginName);

		if($plugin === null){
			return;
		}

		[$highestVersion, $artifactUrl, $api, $err] = $this->getResult();

		if($err !== null){
			$plugin->getLogger()->error("Update notify error: " . $err);
			return;
		}

		if($highestVersion !== $this->pluginVersion){
			$artifactUrl = $artifactUrl . "/" . $this->pluginName . "_" . $highestVersion . ".phar";
			$plugin->getLogger()->notice(vsprintf("Version %s has been released for API %s. Download the new release at %s", [$highestVersion, $api, $artifactUrl]));
		}
	}
}