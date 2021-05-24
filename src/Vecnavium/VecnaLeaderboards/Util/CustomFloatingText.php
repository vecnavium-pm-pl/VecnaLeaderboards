<?php

namespace Vecnavium\VecnaLeaderboards\Util;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Server;
use pocketmine\utils\UUID;

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
	 */
	public function __construct(string $text, Position $position)
	{
		$this->text = $text;
		$this->position = $position;
		$this->eid = Entity::$entityCount++;
	}


	public function spawn(): void
	{
		$pk = new AddPlayerPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->uuid = UUID::fromRandom();
		$pk->username = $this->text;
		$pk->entityUniqueId = $this->eid;
		$pk->position = $this->position->asVector3();
		$pk->item = Item::get(Item::AIR);
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
			foreach ($level->getPlayers() as $player) {
				$player->sendDataPacket($pk);
			}
		}
	}

	public function update(string $text)
	{
		$pk = new SetActorDataPacket();
		$pk->entityRuntimeId = $this->eid;
		$pk->metadata = [
			Entity::DATA_NAMETAG => [
				Entity::DATA_TYPE_STRING, $text
			]
		];
		$level = $this->position->getLevel();
		if ($level !== null) {
			foreach ($level->getPlayers() as $player) {
				$player->sendDataPacket($pk);
			}
		}

	}

	public function remove()
	{
		$pk = new RemoveActorPacket();
		$pk->entityUniqueId = $this->eid;
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$player->sendDataPacket($pk);
		}
	}

}