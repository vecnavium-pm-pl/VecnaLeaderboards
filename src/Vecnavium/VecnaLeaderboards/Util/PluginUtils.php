<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Util;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;

/**
 * Class PluginUtils
 * @package Vecnavium\VecnaLeaderboards\Util
 */
class PluginUtils
{

	/**
	 * @param string $string
	 * @return string
	 */
	public static function colorize(string $string): string
	{
		return str_replace("&", "§", $string);
	}

	/**
	 * @param Position $position
	 * @return string
	 */
	public static function positionToString(Position $position): string
	{
		$vector = $position->asVector3();
		return round($vector->getX(), 2) . "_" . round($vector->getY(), 2) . "_" .
			round($vector->getZ(), 2) . "_" . $position->getWorld()->getFolderName();
	}

	/**
	 * @param string $string
	 * @return Position
	 */
    public static function positionFromString(string $string): Position
    {
        $coords = explode("_", $string);
        $vector3 = new Vector3(floatval($coords[0]), floatval($coords[1]), floatval($coords[2]));
        $level = Server::getInstance()->getWorldManager()->getWorldByName($coords[3]);
        return Position::fromObject($vector3, $level);
    }
}