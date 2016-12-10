<?php
namespace DrdPlus\Tests\Health\Afflictions;

use DrdPlus\Health\Afflictions\AfflictionByWound;
use DrdPlus\Health\Afflictions\AfflictionDangerousness;
use DrdPlus\Health\Afflictions\AfflictionDomain;
use DrdPlus\Health\Afflictions\AfflictionName;
use DrdPlus\Health\Afflictions\AfflictionProperty;
use DrdPlus\Health\Afflictions\AfflictionSize;
use DrdPlus\Health\Afflictions\AfflictionSource;
use DrdPlus\Health\Afflictions\AfflictionVirulence;
use DrdPlus\Health\Afflictions\Effects\AfflictionEffect;
use DrdPlus\Health\Afflictions\ElementalPertinence\ElementalPertinence;
use DrdPlus\Health\GridOfWounds;
use DrdPlus\Health\Health;
use DrdPlus\Health\OrdinaryWound;
use DrdPlus\Health\SeriousWound;
use DrdPlus\Health\SeriousWoundOrigin;
use DrdPlus\Health\WoundOrigin;
use DrdPlus\Health\WoundSize;
use DrdPlus\Properties\Derived\WoundBoundary;
use Granam\Tests\Tools\TestWithMockery;

abstract class AfflictionByWoundTest extends TestWithMockery
{

    /**
     * @test
     * @expectedException \DrdPlus\Health\Afflictions\Exceptions\WoundHasToBeFreshForAffliction
     */
    public function I_can_not_create_it_with_old_wound()
    {
        $reflection = new \ReflectionClass(self::getSutClass());
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);

        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke(
            $instance,
            $this->createWound(true /* serious */, true /* old */),
            $this->mockery(AfflictionDomain::class),
            $this->mockery(AfflictionVirulence::class),
            $this->mockery(AfflictionSource::class),
            $this->mockery(AfflictionProperty::class),
            $this->mockery(AfflictionDangerousness::class),
            $this->mockery(AfflictionSize::class),
            $this->mockery(ElementalPertinence::class),
            $this->mockery(AfflictionEffect::class),
            $this->mockery(\DateInterval::class),
            $this->mockery(AfflictionName::class)
        );
    }

    /**
     * @test
     */
    public function It_is_linked_with_health_immediately()
    {
        $woundBoundary = $this->mockery(WoundBoundary::class);
        $woundBoundary->shouldReceive('getValue')
            ->andReturn(5);
        /** @var WoundBoundary $woundBoundary */
        $health = new Health($woundBoundary);
        $woundSize = $this->mockery(WoundSize::class);
        $woundSize->shouldReceive('getValue')
            ->andReturn(5);
        /** @var WoundSize $woundSize */
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $seriousWound = $health->createWound(
            $woundSize,
            SeriousWoundOrigin::getMechanicalCutWoundOrigin(),
            $woundBoundary
        );
        $afflictionReflection = new \ReflectionClass(self::getSutClass());
        $afflictionConstructor = $afflictionReflection->getConstructor();
        $afflictionConstructor->setAccessible(true);

        $afflictionInstance = $afflictionReflection->newInstanceWithoutConstructor();
        $afflictionConstructor->invoke(
            $afflictionInstance,
            $seriousWound,
            $this->mockery(AfflictionDomain::class),
            $this->mockery(AfflictionVirulence::class),
            $this->mockery(AfflictionSource::class),
            $this->mockery(AfflictionProperty::class),
            $this->mockery(AfflictionDangerousness::class),
            $this->mockery(AfflictionSize::class),
            $this->mockery(ElementalPertinence::class),
            $this->mockery(AfflictionEffect::class),
            $this->mockery(\DateInterval::class),
            $this->mockery(AfflictionName::class)
        );

        self::assertSame([$afflictionInstance], $health->getAfflictionsByWound()->toArray());
    }

    /**
     * @test
     */
    abstract public function I_can_use_it();

    /**
     * @param bool $isSerious
     * @param bool $isOld
     * @param $value
     * @param WoundOrigin $woundOrigin
     * @return \Mockery\MockInterface|SeriousWound|OrdinaryWound
     */
    protected function createWound($isSerious = true, $isOld = false, $value = 0, WoundOrigin $woundOrigin = null)
    {
        $wound = $this->mockery($isSerious ? SeriousWound::class : OrdinaryWound::class);
        $wound->shouldReceive('getHealth')
            ->andReturn($health = $this->mockery(Health::class));
        $health->shouldReceive('addAffliction')
            ->with(\Mockery::type(self::getSutClass()));
        $wound->shouldReceive('isSerious')
            ->andReturn($isSerious);
        $wound->shouldReceive('isOld')
            ->andReturn($isOld);
        $wound->shouldReceive('getWoundSize')
            ->andReturn($woundSize = $this->mockery(WoundSize::class));
        $woundSize->shouldReceive('getValue')
            ->andReturn($value);
        $wound->shouldReceive('__toString')
            ->andReturn((string)$value);
        $wound->shouldReceive('getWoundOrigin')
            ->andReturn($woundOrigin ?: SeriousWoundOrigin::getElementalWoundOrigin());

        return $wound;
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|WoundBoundary
     */
    protected function createWoundBoundary($value)
    {
        $woundBoundary = $this->mockery(WoundBoundary::class);
        $woundBoundary->shouldReceive('getValue')
            ->andReturn($value);

        return $woundBoundary;
    }

    /**
     * @return \Mockery\MockInterface|AfflictionVirulence
     */
    protected function createAfflictionVirulence()
    {
        return $this->mockery(AfflictionVirulence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionProperty
     */
    protected function createAfflictionProperty()
    {
        return $this->mockery(AfflictionProperty::class);
    }

    /**
     * @param $value
     * @return \Mockery\MockInterface|AfflictionSize
     */
    protected function createAfflictionSize($value = null)
    {
        $afflictionSize = $this->mockery(AfflictionSize::class);
        if ($value !== null) {
            $afflictionSize->shouldReceive('getValue')
                ->andReturn($value);
        }

        return $afflictionSize;
    }

    /**
     * @return \Mockery\MockInterface|ElementalPertinence
     */
    protected function createElementalPertinence()
    {
        return $this->mockery(ElementalPertinence::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionEffect
     */
    protected function createAfflictionEffect()
    {
        return $this->mockery(AfflictionEffect::class);
    }

    /**
     * @return \Mockery\MockInterface|\DateInterval
     */
    protected function createOutbreakPeriod()
    {
        return $this->mockery(\DateInterval::class);
    }

    /**
     * @return \Mockery\MockInterface|AfflictionName
     */
    protected function createAfflictionName()
    {
        return $this->mockery(AfflictionName::class);
    }

    /**
     * @param SeriousWound $wound
     * @param WoundBoundary $woundBoundary
     * @param int $filledHalfOfRows
     */
    protected function addSizeCalculation(SeriousWound $wound, WoundBoundary $woundBoundary, $filledHalfOfRows)
    {
        /** @var SeriousWound $wound */
        $health = $wound->getHealth();
        /** @var \Mockery\MockInterface $health */
        $health->shouldReceive('getGridOfWounds')
            ->andReturn($gridOfWounds = $this->mockery(GridOfWounds::class));
        $gridOfWounds->shouldReceive('calculateFilledHalfRowsFor')
            ->with($wound->getWoundSize(), $woundBoundary)
            ->andReturn($filledHalfOfRows);
    }

    /**
     * @test
     */
    public function I_get_will_intelligence_and_charisma_malus_zero_as_not_used()
    {
        $afflictionReflection = new \ReflectionClass(self::getSutClass());
        $afflictionConstructor = $afflictionReflection->getConstructor();
        $afflictionConstructor->setAccessible(true);

        /** @var AfflictionByWound $afflictionInstance */
        $afflictionInstance = $afflictionReflection->newInstanceWithoutConstructor();
        self::assertSame(0, $afflictionInstance->getWillMalus());
        self::assertSame(0, $afflictionInstance->getIntelligenceMalus());
        self::assertSame(0, $afflictionInstance->getCharismaMalus());
    }

    /**
     * @test
     */
    abstract public function I_can_get_heal_malus();

    /**
     * @test
     */
    abstract public function I_can_get_malus_to_activities();

    /**
     * @test
     */
    abstract public function I_can_get_strength_malus();

    /**
     * @test
     */
    abstract public function I_can_get_agility_malus();

    /**
     * @test
     */
    abstract public function I_can_get_knack_malus();

}