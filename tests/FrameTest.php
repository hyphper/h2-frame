<?php
declare(strict_types=1);
namespace Hyphper\Test;

class FrameTest extends \PHPUnit_Framework_TestCase
{
    protected function decodeFrame($frame_data)
    {
        $f = \Hyphper\Frame::parseFrameHeader(substr($frame_data, 0, 9));
        $length = $f->getLength();

        $f->parseBody(substr($frame_data, 9, $length));
        $this->assertEquals(9 + $length, strlen($frame_data));
        return $f;
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\UnknownFrameException
     * @expectedExceptionMessage Unknown frame type 0xFF received, length 89 bytes
     */
    public function testParseFrameHeaderUnknownType()
    {
        try {
            \Hyphper\Frame::parseFrameHeader("\x00\x00\x59\xFF\x00\x00\x00\x00\x01");
        } catch (\Hyphper\Frame\Exception\UnknownFrameException $e) {
            $this->assertEquals(0xFF, $e->getFrameType());
            $this->assertEquals(0x59, $e->getLength());
            throw $e;
        }
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testCannotParseInvalidFrameHeader()
    {
        \Hyphper\Frame::parseFrameHeader("\x00\x00\x08\x00\x01\x00\x00\x00");
    }
}
