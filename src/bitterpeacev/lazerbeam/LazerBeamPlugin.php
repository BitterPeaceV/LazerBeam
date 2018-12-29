<?php

namespace bitterpeacev\lazerbeam;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\math\Vector3;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class LazerBeamPlugin extends PluginBase implements Listener
{
    /** @var LazerBeam[] */
    private $beams;

    public function onEnable()
    {
        foreach ($this->getConfig()->getAll() as $id => $beam) {
            $name = $beam[0];
            $class = "pocketmine\\level\\particle\\{$name}Particle";
            $effects = [];
            foreach ($beam[4] as $data) {
                $elements = explode("/", $data);
                $effects[] = new EffectInstance(Effect::getEffect($elements[0]), $elements[1] * 20, $elements[2] - 1);
            }
            $this->beams[$id] = new LazerBeam(new $class(new Vector3()), $beam[1], $beam[2], $beam[3], $effects);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPacketRecieve(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof LevelSoundEventPacket) {
            if ($packet->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE) {
                $player = $event->getPlayer();
                $id = $player->getInventory()->getItemInHand()->getId();
                if (isset($this->beams[$id])) {
                    $this->beams[$id]->shot($player);
                }
            }
        }
    }
}
