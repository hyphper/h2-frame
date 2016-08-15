<?php
namespace Hyphper\Test;

class PingFrameTest extends \Hyphper\Test\FrameTest
{
    public function testPingFrameHasOnlyOneFlag()
    {
        $f = new \Hyphper\Frame\PingFrame();
        $flags = $f->parseFlags(0xFF);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK],
            $flags->getIterator()
        );
    }

    public function testPingFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\PingFrame();
        $f->parseFlags(0xFF);
        $f->setOpaqueData("\x01\x02");

        $s = $f->serialize();
        $this->assertEquals(
            "\x00\x00\x08\x06\x01\x00\x00\x00\x00\x01\x02\x00\x00\x00\x00\x00\x00",
            $s
        );
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage PING frame may not have more than 8 bytes of data, got 9
     */
    public function testNoMoreThan8Octets()
    {
        $f = new \Hyphper\Frame\PingFrame();
        $f->setOpaqueData("\x01\x02\x03\x04\x05\x06\x07\x08\x09");
        $f->serialize();
    }

    public function testPingFrameParsesProperly()
    {
        $s = "\x00\x00\x08\x06\x01\x00\x00\x00\x00\x01\x02\x00\x00\x00\x00\x00\x00";
        $f = $this->decodeFrame($s);

        $this->assertInstanceOf(\Hyphper\Frame\PingFrame::class, $f);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK],
            $f->getFlags()->getIterator()
        );
        $this->assertEquals("\x01\x02\x00\x00\x00\x00\x00\x00", $f->getOpaqueData());
        $this->assertEquals(8, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testPingFrameNeverHasAStream()
    {
        new \Hyphper\Frame\PingFrame(['stream_id' => 1]);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage PING frame must have 8 byte length, got 9
     */
    public function testPingFrameHasNoMoreThanBodyLength8()
    {
        $f = new \Hyphper\Frame\PingFrame();
        $f->parseBody("\x01\x02\x03\x04\x05\x06\x07\x08\x09");
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage PING frame must have 8 byte length, got 7
     */
    public function testPingFrameHasNoLessThanBodyLength8()
    {
        $f = new \Hyphper\Frame\PingFrame();
        $f->parseBody("\x01\x02\x03\x04\x05\x06\x07");
    }
}
