<?php
declare(strict_types=1);
namespace Hyphper\Test;

/**
 * h2-frame
 *
 * @author Davey Shafik <dshafik@akamai.com>
 * @copyright Copyright 2016 Akamai Technologies, Inc. All rights reserved.
 * @license Apache 2.0
 * @link https://github.com/akamai-open/h2-frame
 * @link https://developer.akamai.com
 */
class FlagsTest extends PHPUnit_Framework_TestCase
{
    const VALID_FLAG = 0x00;

    public function testFlags()
    {
        $flags = new \Hyphper\Frame\Flags(
            \Hyphper\Frame\Flag::ACK,
            \Hyphper\Frame\Flag::PADDED
        );

        $flags->add(\Hyphper\Frame\Flag::ACK);
        $flags->add(\Hyphper\Frame\Flag::PADDED);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFlagException
     * @expectedExceptionMessage Unexpected flag: 0x01. Valid flags are: none
     */
    public function testInvalidFlagsNoValid()
    {
        $flags = new \Hyphper\Frame\Flags();
        $flags->add(\Hyphper\Frame\Flag::ACK);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFlagException
     * @expectedExceptionMessage Unexpected flag: 0x01. Valid flags are: 0x08, 0x04
     */
    public function testInvalidFlags()
    {
        $flags = new \Hyphper\Frame\Flags(
            \Hyphper\Frame\Flag::PADDED,
            \Hyphper\Frame\Flag::END_HEADERS
        );

        $flags->add(\Hyphper\Frame\Flag::ACK);
    }

    /**
     * From hyperframe
     */

    public function testAdd()
    {

        $flags = new \Hyphper\Frame\Flags(self::VALID_FLAG);
        $this->assertEquals([], $flags->getIterator());

        $flags->add(self::VALID_FLAG);
        $flags->add(self::VALID_FLAG);

        $this->assertArrayHasKey(self::VALID_FLAG, $flags->getIterator());
        $this->assertEquals([self::VALID_FLAG => self::VALID_FLAG], $flags->getIterator());
        $this->assertCount(1, $flags);
    }

    public function testRemove()
    {
        $flags = new \Hyphper\Frame\Flags(self::VALID_FLAG);
        $flags->add(self::VALID_FLAG);
        $flags->remove(self::VALID_FLAG);
        $this->assertArrayNotHasKey(self::VALID_FLAG, $flags->getIterator());
        $this->assertEquals([], $flags->getIterator());
        $this->assertCount(0, $flags);

        $this->assertNull($flags->remove(\Hyphper\Frame\Flag::END_STREAM));
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFlagException
     * @expectedExceptionMessage Unexpected flag: 0x01. Valid flags are: 0x00
     */
    public function testValidation()
    {
        $flags = new \Hyphper\Frame\Flags(self::VALID_FLAG);
        $flags->add(self::VALID_FLAG);
        $flags->add(\Hyphper\Frame\Flag::ACK);
    }
}
