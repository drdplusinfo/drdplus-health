<?php
namespace DrdPlus\Person\Health\Afflictions\Effects;

use DrdPlus\Person\Health\Afflictions\SpecificAfflictions\CrackedBones;

class CrackedBonesEffect extends AfflictionEffect
{
    const CRACKED_BONES = 'cracked_bones';

    /**
     * @return CrackedBonesEffect
     */
    public static function getIt()
    {
        return static::getEnum(self::CRACKED_BONES);
    }

    /**
     * {@inheritdoc}
     */
    public function isEffectiveEvenOnSuccessAgainst()
    {
        return true;
    }

    /**
     * @param CrackedBones $crackedBones
     * @return int
     */
    public function getToHealingMalus(CrackedBones $crackedBones)
    {
        // note: affliction size is always at least zero
        return -$crackedBones->getSize()->getValue();
    }

}