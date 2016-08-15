<?php
namespace Hyphper\Test;

class ContinuationFrameTest extends FrameTest
{
    public function testContinuationFrameFlags()
    {
        $f = new \Hyphper\Frame\ContinuationFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS],
            $flags->getIterator()
        );
    }

    public function testContinuationFrameSerializes()
    {
        $f = new \Hyphper\Frame\ContinuationFrame(['stream_id' => 1]);
        $f->parseFlags(0x04);
        $f->setData('hello world');

        $s = $f->serialize();
        $this->assertEquals("\x00\x00\x0B\x09\x04\x00\x00\x00\x01hello world", $s);
    }

    public function testContinuationFrameParsesProperly()
    {
        $s = "\x00\x00\x0B\x09\x04\x00\x00\x00\x01hello world";
        $f = $this->decodeFrame($s);

        $this->assertInstanceOf(\Hyphper\Frame\ContinuationFrame::class, $f);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS],
            $f->getFlags()->getIterator()
        );
        $this->assertEquals('hello world', $f->getData());
        $this->assertEquals(11, $f->getBodyLen());
    }
}
