<?php
namespace Hyphper\Test;

class GoAwayFrameTest extends FrameTest
{
    public function testGoAwayFrameNoFlags()
    {
        $f = new \Hyphper\Frame\GoAwayFrame();
        $flags = $f->parseFlags(0xFF);

        $this->assertInstanceOf(\Hyphper\Frame\Flags::class, $flags);
        $this->assertEmpty($flags->getIterator());
    }

    public function testGoAwayFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\GoAwayFrame();
        $f->setLastStreamId(64);
        $f->setErrorCode(32);
        $f->setAdditionalData('hello');

        $s = $f->serialize();
        $this->assertEquals(
            "\x00\x00\x0D\x07\x00\x00\x00\x00\x00" .  // Frame header
            "\x00\x00\x00\x40" .                      // Last Stream ID
            "\x00\x00\x00\x20" .                      // Error Code
            "hello",                                  // Additional data
            $s
        );
    }

    public function testGoAwayFrameParsesProperly()
    {
        $s = "\x00\x00\x0D\x07\x00\x00\x00\x00\x00" .  // Frame header
             "\x00\x00\x00\x40" .                      // Last Stream ID
             "\x00\x00\x00\x20" .                      // Error Code
             "hello";                                  // Additional data

        $f = $this->decodeFrame($s);

        $this->assertInstanceOf(\Hyphper\Frame\GoAwayFrame::class, $f);
        $this->assertEquals([], $f->getFlags()->getIterator());
        $this->assertEquals('hello', $f->getAdditionalData());
        $this->assertEquals(13, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testGoAwayFrameNeverHasAStream()
    {
        $f = new \Hyphper\Frame\GoAwayFrame(['stream_id' => 1]);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Invalid GOAWAY body.
     */
    public function testShortGoAwayFrameErrors()
    {
        $s = "\x00\x00\x0D\x07\x00\x00\x00\x00\x00" .  // Frame header
             "\x00\x00\x00\x40" .                      // Last Stream ID
             "\x00\x00\x00";                           // short Error Code"

        $this->decodeFrame($s);
    }
}
