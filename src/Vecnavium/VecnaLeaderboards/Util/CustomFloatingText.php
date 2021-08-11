<?php

/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Util;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\utils\UUID;

/**
 * Class CustomFloatingText
 * @package Vecnavium\VecnaLeaderboards\Util
 */
class CustomFloatingText
{
	/** @var int */
	private $eid;
	/** @var string */
	private $text;
	/** @var Position */
	private $position;

	/**
	 * CustomFloatingText constructor.
	 * @param string $text
	 * @param Position $position
	 * @param int $eid
	 */
	public function __construct(string $text, Position $position, int $eid)
	{
		$this->text = $text;
		$this->position = $position;
		$this->eid = $eid;
	}


	/**
	 * @param Player $player
	 */
	public function spawn(Player $player): void
	{
		$pk = new AddPlayerPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->uuid = UUID::fromRandom();
		$pk->username = $this->text;
		$pk->entityUniqueId = $this->eid;
		$pk->position = $this->position->asVector3();
		$pk->item = ItemStackWrapper::legacy(ItemFactory::get(Item::AIR, 0, 0));
		$flags =
			1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG |
			1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG |
			1 << Entity::DATA_FLAG_IMMOBILE;
		$pk->metadata = [
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
		];
		$level = $this->position->getLevel();
		if ($level !== null) {
			$player->sendDataPacket($pk);
		}
	}

	/**
	 * @param string $text
	 * @param Player $player
	 */
	public function update(string $text, Player $player): void
	{
		$pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->metadata = [
			Entity::DATA_NAMETAG => [
				Entity::DATA_TYPE_STRING, $text
			]
		];
		$player->sendDataPacket($pk);
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		$pk = new RemoveActorPacket();
		$pk->entityUniqueId = $this->eid;
		$player->sendDataPacket($pk);
	}

}
