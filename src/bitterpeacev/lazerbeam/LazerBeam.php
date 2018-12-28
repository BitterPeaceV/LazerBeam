<?php

namespace bitterpeacev\lazerbeam;

use pocketmine\Player;
use pocketmine\level\particle\Particle;
use pocketmine\entity\EffectInstance;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class LazerBeam
{
    /** @var Particle */
    private $particle;
    /** @var int */
    private $range;
    /** @var float */
    private $damage;
    /** @var float */
    private $knockBack;
    /** @var EffectInstance[] */
    private $effects;

    /**
     * @param Particle         $particle  パーティクル
     * @param int              $range     長さ
     * @param float            $damage    攻撃力
     * @param float            $knockBack ノックバック
     * @param EffectInstance[] $effects   エフェクト
     */
    public function __construct(Particle $particle, int $range, float $damage, float $knockBack = 0.4, array $effects = [])
    {
        $this->particle = $particle;
        $this->range = $range;
        $this->damage = $damage;
        $this->knockBack = $knockBack;
        $this->effects = $effects;
    }

    /**
     * ビームを撃つ
     * 
     * @param Player $shooter ビームを撃つプレイヤー
     */
    public function shot(Player $shooter)
    {
        // ビームの始点を設定
        $start = $shooter->getPosition()->add(0, $shooter->getEyeHeight());
        $this->particle->setComponents($start->x, $start->y, $start->z);

        $increase = $shooter->getDirectionVector()->normalize();
        for ($i = 0; $i < $this->range; $i++) {
            $pos = $this->particle->add($increase);

            // ブロックを通り抜けられない場合、ループを抜ける
            if (!$shooter->level->getBlock($pos)->canBeFlowedInto()) break;

            // パーティクルを表示
            $this->particle->setComponents($pos->x, $pos->y, $pos->z);
            $shooter->level->addParticle($this->particle);

            // ビームの当たり判定
            foreach ($shooter->level->getPlayers() as $player) {
                // 他のプレイヤーに当たった場合
                if ($player->distance($pos) < 1.5 && $shooter !== $player) {
                    // ダメージとエフェクトを与える
                    $event = new EntityDamageByEntityEvent(
                        $shooter,
                        $player,
                        EntityDamageEvent::CAUSE_PROJECTILE,
                        $this->damage,
                        [],
                        $this->knockBack
                    );
                    $player->attack($event);
                    // EffectInstanceを流用すると二回目以降は付与されない(謎)のでcloneしました
                    foreach ($this->effects as $effect) $player->addEffect(clone $effect);

                    // 当たったのでループを抜ける
                    break 2;
                }
            }
        }

        // パーティクルの座標の初期化
        $this->particle->setComponents(0, 0, 0);
    }
}
