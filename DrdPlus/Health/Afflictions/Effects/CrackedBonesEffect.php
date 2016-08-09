<?php
namespace DrdPlus\Health\Afflictions\Effects;

use DrdPlus\Health\Afflictions\SpecificAfflictions\CrackedBones;

/**
 * @method static CrackedBonesEffect getEnum($enumValue)
 */
class CrackedBonesEffect extends AfflictionEffect
{
    const CRACKED_BONES_EFFECT = 'cracked_bones_effect';

    /**
     * @return CrackedBonesEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::CRACKED_BONES_EFFECT);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainstTrap()
    {
        return true;
    }

    /**
     * @param CrackedBones $crackedBones
     * @return int
     */
    public function getHealingMalus(CrackedBones $crackedBones)
    {
        // note: affliction size is always at least zero, therefore this malus is at least zero or less
        return -$crackedBones->getSize()->getValue();
    }

}