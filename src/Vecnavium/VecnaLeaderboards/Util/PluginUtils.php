<?php

namespace Vecnavium\VecnaLeaderboards\Util;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class PluginUtils
{

	/**
	 * @param string $string
	 * @return array|string|string[]
	 */
	public static function colorize(string $string)
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
			round($vector->getZ(), 2) . "_" . $position->level->getFolderName();
	}

	/**
	 * @param string $string
	 * @return Position
	 */
	public static function positionFromString(string $string): Position
	{
		$coords = explode("_", $string);
		$vector3 = new Vector3($coords[0], $coords[1], $coords[2]);
		$level = Server::getInstance()->getLevelByName($coords[3]);
		return Position::fromObject($vector3, $level);
	}
}