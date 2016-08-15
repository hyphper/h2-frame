<?php
namespace Hyphper\Test;

class WindowUpdateFrameTest extends FrameTest
{
    public function testWindowUpdateHasNoFlags()
    {
        $f = new \Hyphper\Frame\WindowUpdateFrame(['stream_id' => 0]);
        $flags = $f->parseFlags(0xFF);

        $this->assertInstanceOf(\Hyphper\Frame\Flags::class, $flags);
        $this->assertEmpty($flags->getIterator());
    }

    public function testWindowUpdateSerializsProperly()
    {
        $f = new \Hyphper\Frame\WindowUpdateFrame(['stream_id' => 0]);
        $f->setWindowIncrement(512);

        $s = $f->serialize();
        $this->assertEquals("\x00\x00\x04\x08\x00\x00\x00\x00\x00\x00\x00\x02\x00", $s);
    }

    public function testWindowUpdateFrameParsesProperly()
    {
        $s = "\x00\x00\x04\x08\x00\x00\x00\x00\x00\x00\x00\x02\x00";
        $f = $this->decodeFrame($s);

        $this->assertInstanceOf(\Hyphper\Frame\WindowUpdateFrame::class, $f);
        $this->assertEquals([], $f->getFlags()->getIterator());
        $this->assertEquals(512, $f->getWindowIncrement());
        $this->assertEquals(4, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Invalid WINDOW_UPDATE body
     */
    public function testShortWindowUpdateFrameErrors()
    {
        $s = "\x00\x00\x04\x08\x00\x00\x00\x00\x00\x00\x00\x02"; // -1 byte

        $this->decodeFrame($s);
    }
}
