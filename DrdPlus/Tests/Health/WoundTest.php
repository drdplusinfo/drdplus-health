<?php
namespace DrdPlus\Tests\Health;

use DrdPlus\Health\HealingPower;
use DrdPlus\Health\Health;
use DrdPlus\Health\OrdinaryWoundOrigin;
use DrdPlus\Health\PointOfWound;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\Wound;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Derived\Toughness;
use DrdPlus\Tables\Measurements\Wounds\Wounds;
use DrdPlus\Tables\Measurements\Wounds\WoundsBonus;
use DrdPlus\Tables\Measurements\Wounds\WoundsTable;
use DrdPlus\Tables\Tables;
use Granam\Tests\Tools\TestWithMockery;

abstract class WoundTest extends TestWithMockery
{
    /**
     * @test
     * @return Wound
     */
    public function I_can_use_it(): Wound
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $this->createWound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $woundOrigin = SeriousWoundOrigin::getMechanicalCutWoundOrigin()
        );
        self::assertNull($wound->getId());
        self::assertSame($health, $wound->getHealth());
        self::assertSame($woundSizeValue, $wound->getValue());
        $this->assertIsSeriousAsExpected($wound);
        if ($wound->isSerious()) {
            self::assertSame($woundOrigin, $wound->getWoundOrigin());
        } else {
            self::assertSame(OrdinaryWoundOrigin::getIt(), $wound->getWoundOrigin());
        }
        self::assertFalse($wound->isHealed(), "Wound with {$woundSizeValue} should not be identified as healed");
        $pointsOfWound = $wound->getPointsOfWound();
        self::assertCount($woundSizeValue, $pointsOfWound);
        foreach ($pointsOfWound as $pointOfWound) {
            self::assertInstanceOf(PointOfWound::class, $pointOfWound);
        }
        self::assertFalse($wound->isOld());
        $wound->setOld();
        self::assertTrue($wound->isOld());
        self::assertSame('3', (string)$wound);

        return $wound;
    }

    /**
     * @param Health $health
     * @param WoundSize $woundSize
     * @param SeriousWoundOrigin $seriousWoundOrigin
     * @return Wound
     */
    abstract protected function createWound(Health $health, WoundSize $woundSize, SeriousWoundOrigin $seriousWoundOrigin);

    /**
     * @param bool $openForNewWounds
     * @return \Mockery\MockInterface|Health
     */
    private function createHealth($openForNewWounds = true)
    {
        $health = $this->mockery(Health::class);
        $health->shouldReceive('isOpenForNewWound')
            ->andReturn($openForNewWounds);

        return $health;
    }

    /**
     * @param Wound $wound
     */
    abstract protected function assertIsSeriousAsExpected(Wound $wound);

    /**
     * @test
     */
    public function I_can_heal_it_both_partially_and_fully()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $this->createWound(
            $health = $this->createHealth(),
            new WoundSize($woundSizeValue = 3),
            $elementalWoundOrigin = SeriousWoundOrigin::getElementalWoundOrigin()
        );
        self::assertSame($woundSizeValue, $wound->getValue(), 'Expected same value as created with');
        self::assertCount($woundSizeValue, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());
        $this->assertIsSeriousAsExpected($wound);
        self::assertFalse($wound->isOld());

        self::assertSame(
            1,
            $wound->heal(HealingPower::createForTreatment(123, $this->createTablesWithWoundsTable(3, 123)), $this->createToughness(-2)),
            'Expected reported healed value to be 1'
        );
        self::assertSame(2, $wound->getValue(), 'Expected one point of wound to be already healed');
        self::assertCount(2, $wound->getPointsOfWound());
        self::assertFalse($wound->isHealed());
        self::assertTrue($wound->isOld(), 'Wound should become "old" after any heal attempt');

        self::assertSame(
            2,
            $wound->heal(HealingPower::createForTreatment(123, $this->createTablesWithWoundsTable(999, 123)), $this->createToughness(456)),
            'Expected reported healed value to be the remaining value, 2'
        );
        self::assertEmpty($wound->getPointsOfWound());
        self::assertTrue($wound->isHealed());
        self::assertTrue($wound->isOld(), 'Wound should become "old" after any heal attempt');
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|Toughness
     */
    private function createToughness($value)
    {
        $toughness = $this->mockery(Toughness::class);
        $toughness->shouldReceive('getValue')
            ->andReturn($value);

        return $toughness;
    }

    /**
     * @param $woundsValue
     * @param $expectedWoundsBonus
     * @return \Mockery\MockInterface|Tables
     */
    private function createTablesWithWoundsTable($woundsValue, $expectedWoundsBonus)
    {
        $tables = $this->mockery(Tables::class);
        $tables->shouldReceive('getWoundsTable')
            ->andReturn($woundsTable = $this->mockery(WoundsTable::class));
        $woundsTable->shouldReceive('toWounds')
            ->andReturnUsing(function (WoundsBonus $woundBonus) use ($expectedWoundsBonus, $woundsValue) {
                self::assertSame($expectedWoundsBonus, $woundBonus->getValue());
                $wounds = $this->mockery(Wounds::class);
                $wounds->shouldReceive('getValue')
                    ->andReturn($woundsValue);

                return $wounds;
            });

        return $tables;
    }

    /**
     * @test
     */
    public function I_can_create_wound_with_zero_value()
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $wound = $this->createWound(
            $this->createHealth(),
            new WoundSize(0),
            SeriousWoundOrigin::getMechanicalCrushWoundOrigin()
        );
        self::assertSame(0, $wound->getValue());
        self::assertTrue($wound->isHealed());
        self::assertFalse($wound->isOld());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Health\Exceptions\WoundHasToBeCreatedByHealthItself
     */
    public function I_can_not_create_wound_directly()
    {
        $this->createWound(
            $this->createHealth(false /* not open for new wounds */),
            new WoundSize(1),
            SeriousWoundOrigin::getMechanicalCrushWoundOrigin()
        );
    }
}