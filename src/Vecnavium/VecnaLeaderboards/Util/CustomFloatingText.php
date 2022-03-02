<?php


/**
 * Copyright (c) 2021 Vecnavium
 * VecnaLeaderboards is licensed under the GNU Lesser General Public License v3.0
 * GitHub: https://github.com/Vecnavium\VecnaLeaderboards
 */

declare(strict_types=1);
namespace Vecnavium\VecnaLeaderboards\Util;

use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Ramsey\Uuid\Uuid;


/**
 * Class CustomFloatingText
 * @package Vecnavium\VecnaLeaderboards\Util
 */
class CustomFloatingText
{
	/** @var int */
	private int $eid;
	/** @var string */
	private string $text;
	/** @var Position */
	private Position $position;

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
		$pk->actorRuntimeId = $this->eid;
		$pk->uuid = Uuid::uuid4();
		$pk->username = $this->text;
		$pk->actorUniqueId = $this->eid;
		$pk->position = $this->position->asVector3();
		$pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(ItemFactory::air()));
		$flags =
			1 << EntityMetadataFlags::CAN_SHOW_NAMETAG |
			1 << EntityMetadataFlags::ALWAYS_SHOW_NAMETAG |
			1 << EntityMetadataFlags::IMMOBILE;
		$pk->metadata = [
			EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
			EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01) //zero causes problems on debug builds
		];

		$level = $this->position->getWorld();
		if ($level !== null) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param string $text
	 * @param Player $player
	 */
	public function update(string $text, Player $player): void
	{
		$pk = new SetActorDataPacket();
		$pk->actorRuntimeId = $this->eid;
		$pk->metadata = [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($text)];
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		$pk = new RemoveActorPacket();
		$pk->actorUniqueId = $this->eid;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

}
