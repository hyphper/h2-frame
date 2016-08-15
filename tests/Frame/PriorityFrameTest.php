<?php
namespace Hyphper\Test;

class PriorityFrameTest extends FrameTest
{
    protected $payload = "\x00\x00\x05\x02\x00\x00\x00\x00\x01\x80\x00\x00\x04\x40";

    public function testPriorityFrameHasNoFlags()
    {
        $f = new \Hyphper\Frame\PriorityFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);

        $this->assertInstanceOf(\Hyphper\Frame\Flags::class, $flags);
        $this->assertEquals([], $flags->getIterator());
    }

    public function testPriorityFrameDefaultSerializesProperly()
    {
        $f = new \Hyphper\Frame\PriorityFrame(['stream_id' => 1]);
        $this->assertEquals("\x00\x00\x05\x02\x00\x00\x00\x00\x01\x00\x00\x00\x00\x00", $f->serialize());
    }
    
    public function testPriorityFrameWithAllDataSerializesProperly()
    {
        $f = new \Hyphper\Frame\PriorityFrame(['stream_id' => 1]);
        $f->setDependsOn(0x04);
        $f->setStreamWeight(64);
        $f->setExclusive(true);
        $this->assertEquals($this->payload, $f->serialize());
    }

    public function testPriorityFrameWithAllDataParsesProperly()
    {
        $f = $this->decodeFrame($this->payload);
        $this->assertInstanceOf(\Hyphper\Frame\PriorityFrame::class, $f);
        $this->assertEquals([], $f->getFlags()->getIterator());
        $this->assertEquals(4, $f->getDependsOn());
        $this->assertEquals(64, $f->getStreamWeight());
        $this->assertEquals(true, $f->getExclusive());
        $this->assertEquals(5, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testPriorityFrameCometsOnAStream()
    {
        new \Hyphper\Frame\PriorityFrame(['stream_id' => 0]);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Invalid Priority data
     */
    public function testShortPriorityFrameErrors()
    {
        $this->decodeFrame(substr($this->payload, 0, -2));
    }
}
