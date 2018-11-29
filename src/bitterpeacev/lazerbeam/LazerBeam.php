<?php

namespace bitterpeacev\lazerbeam;

use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\particle\Particle;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class LazerBeam
{
    /**
     * ビームを撃つ
     * 
     * @param Player   $player   ビームを撃つプレイヤー
     * @param Particle $particle ビームのパーティクル
     * @param float    $range    ビームの長さ
     * @param float    $damage   ビームの攻撃力
     */
    public static function beam(Player $player, Particle $particle, float $range, float $damage)
    {
        // ビームの始点を設定
        $start = $player->getPosition()->add(0, $player->getEyeHeight());
        $particle->setComponents($start->x, $start->y, $start->z);

        $increase = $player->getDirectionVector();
        $distance = 0;
        while ($distance < $range) {
            $pos = $particle->add($increase);
            $distance += $particle->distance($pos);

            // ブロックを通り抜けれない場合、ループから抜ける
            if (!$player->level->getBlock($pos)->canBeFlowedInto()) break;

            // ビームの当たり判定
            foreach ($player->level->getPlayers() as $p) {
                if ($p->distance($pos) < 2 && $p != $player) {
                    $ev = new EntityDamageByEntityEvent($player, $p, EntityDamageByEntityEvent::CAUSE_MAGIC, $damage);
                    $p->attack($ev);

                    break 2;
                }
            }

            $particle->setComponents($pos->x, $pos->y, $pos->z);
            $player->level->addParticle($particle);
        }
    }
}