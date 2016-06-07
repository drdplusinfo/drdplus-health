<?php
namespace DrdPlus\Health\Afflictions;

use Doctrineum\String\StringEnum;
use DrdPlus\Codes\PropertyCodes;
use Granam\Tools\ValueDescriber;

class AfflictionProperty extends StringEnum
{
    /**
     * @param string $propertyCode
     * @return AfflictionProperty
     */
    public static function getIt($propertyCode)
    {
        return self::getEnum($propertyCode);
    }

    protected static function convertToEnumFinalValue($enumValue)
    {
        $enumFinalValue = parent::convertToEnumFinalValue($enumValue);
        if (!in_array($enumFinalValue, self::getProperties(), true)) {
            throw new Exceptions\UnknownAfflictionPropertyCode(
                'Got unknown code of property keeping affliction on short: ' . ValueDescriber::describe($enumValue)
            );
        }

        return $enumFinalValue;
    }

    const LEVEL = 'level';

    /**
     * @return array|string[]
     */
    public static function getProperties()
    {
        return [
            PropertyCodes::STRENGTH,
            PropertyCodes::AGILITY,
            PropertyCodes::KNACK,
            PropertyCodes::WILL,
            PropertyCodes::INTELLIGENCE,
            PropertyCodes::CHARISMA,
            PropertyCodes::ENDURANCE,
            PropertyCodes::TOUGHNESS,
            self::LEVEL,
        ];
    }

}