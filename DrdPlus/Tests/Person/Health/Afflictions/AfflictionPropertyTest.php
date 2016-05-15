<?php
namespace DrdPlus\Tests\Person\Health\Afflictions;

use DrdPlus\Person\Health\Afflictions\AfflictionProperty;

class AfflictionPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider providePropertyCode
     * @param string $propertyCode
     */
    public function I_can_use_it($propertyCode)
    {
        $afflictionProperty = AfflictionProperty::getIt($propertyCode);
        self::assertInstanceOf(AfflictionProperty::class, $afflictionProperty);
        self::assertSame($propertyCode, $afflictionProperty->getValue());
    }

    public function providePropertyCode()
    {
        return array_map(
            function ($propertyCode) {
                return [$propertyCode];
            },
            $this->getPropertyCodes()
        );
    }

    private function getPropertyCodes()
    {
        return [
            'strength',
            'agility',
            'knack',
            'will',
            'intelligence',
            'charisma',
            'endurance',
            'toughness',
            'level',
        ];
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Afflictions\Exceptions\UnknownAfflictionPropertyCode
     * @expectedExceptionMessageRegExp ~greedy~
     */
    public function I_can_not_use_custom_property()
    {
        AfflictionProperty::getIt('greedy');
    }
}
