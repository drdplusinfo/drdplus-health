<?php
namespace DrdPlus\Tests\Person\Health;

use DrdPlus\Person\Health\WoundSize;
use Granam\Integer\IntegerInterface;
use Granam\Tests\Tools\TestWithMockery;

class WoundSizeTest extends TestWithMockery
{
    /**
     * @test
     */
    public function I_can_use_it_as_an_integer()
    {
        $woundSize = new WoundSize(123);
        self::assertInstanceOf(IntegerInterface::class, $woundSize);
        self::assertSame(123, $woundSize->getValue());
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\WoundValueHasToBeAtLeastZero
     * @expectedExceptionMessageRegExp ~Terribly wounded by horrible pebble~
     */
    public function I_am_stopped_by_specific_exception_on_invalid_value()
    {
        new WoundSize('Terribly wounded by horrible pebble');
    }

    /**
     * @test
     * @expectedException \DrdPlus\Person\Health\Exceptions\WoundValueHasToBeAtLeastZero
     * @expectedExceptionMessageRegExp ~-1~
     */
    public function I_can_not_use_negative_value()
    {
        new WoundSize(-1);
    }
}
