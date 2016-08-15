<?php
namespace Hyphper\Test;

class RstStreamFrameTest extends FrameTest
{
    public function testRstStreamFrameHasNoFlags()
    {
        $f = new \Hyphper\Frame\RstStreamFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);
        $this->assertInstanceOf(\Hyphper\Frame\Flags::class, $flags);
        $this->assertEmpty($flags->getIterator());
    }

    public function testRstStreamFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\RstStreamFrame(['stream_id' => 1]);
        $f->setErrorCode(420);

        $s = $f->serialize();
        $this->assertEquals($s, "\x00\x00\x04\x03\x00\x00\x00\x00\x01\x00\x00\x01\xa4");
    }

    public function testRstStreamFrameParsesProperly()
    {
        $s = "\x00\x00\x04\x03\x00\x00\x00\x00\x01\x00\x00\x01\xa4";
        $f = $this->decodeFrame($s);
        $this->assertInstanceOf(\Hyphper\Frame\RstStreamFrame::class, $f);
        $this->assertEquals([], $f->getFlags()->getIterator());
        $this->assertEquals(420, $f->getErrorCode());
        $this->assertEquals(4, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testRstStreamFrameComesOnAStream()
    {
        new \Hyphper\Frame\RstStreamFrame(['stream_id' => 0]);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage RST_STREAM must have 4 byte body: actual length 1.
     */
    public function testRstStreamFrameMustHaveBodyLengthFour()
    {
        $f = new \Hyphper\Frame\RstStreamFrame(['stream_id' => 1]);
        $f->parseBody("\x01");
    }
}
