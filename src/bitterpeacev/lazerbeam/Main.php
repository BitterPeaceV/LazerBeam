<?php

namespace bitterpeacev\lazerbeam;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemIds;
use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerHold(PlayerInteractEvent $event)
    {
        if ($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_AIR && $event->getItem()->getId() == ItemIds::WOODEN_SHOVEL) {
            LazerBeam::beam($event->getPlayer(), new FlameParticle(new Vector3()), 20, 10);
        }
    }
}