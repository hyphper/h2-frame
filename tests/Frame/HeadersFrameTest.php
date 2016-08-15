<?php
namespace Hyphper\Test;


class HeadersFrameTest extends FrameTest
{
    public function testHeadersFrameFlags()
    {
        $f = new \Hyphper\Frame\HeadersFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);

        $this->assertEquals(
            [
                \Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM,
                \Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS,
                \Hyphper\Frame\Flag::PADDED => \Hyphper\Frame\Flag::PADDED,
                \Hyphper\Frame\Flag::PRIORITY => \Hyphper\Frame\Flag::PRIORITY
            ],
            $flags->getIterator()
        );
    }

    public function testHeadersFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\HeadersFrame(['stream_id' => 1]);
        $f->setFlags([
            \Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM,
            \Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS
        ]);
        $f->setData('hello world');

        $s = $f->serialize();
        $this->assertEquals(
            "\x00\x00\x0B\x01\x05\x00\x00\x00\x01hello world",
            $s
        );
    }

    public function testHeadersFrameParsesProperly()
    {
        $s = "\x00\x00\x0B\x01\x05\x00\x00\x00\x01hello world";

        $f = $this->decodeFrame($s);

        $this->assertInstanceOf(\Hyphper\Frame\HeadersFrame::class, $f);
        $this->assertEquals(
            [
                \Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM,
                \Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS
            ],
            $f->getFlags()->getIterator()
        );
        $this->assertEquals('hello world', $f->getData());
        $this->assertEquals(11, $f->getBodyLen());
    }

    public function testHeadersFrameWithPriorityParsesProperly()
    {
        /**
         * This test also tests that we can receive a HEADERS frame with no
         * actual headers on it. This is technically possible.
         */

        $s = "\x00\x00\x05\x01\x20\x00\x00\x00\x01\x80\x00\x00\x04\x40";

        $f = $this->decodeFrame($s);
        $this->assertInstanceOf(\Hyphper\Frame\HeadersFrame::class, $f);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::PRIORITY => \Hyphper\Frame\Flag::PRIORITY],
            $f->getFlags()->getIterator()
        );
        $this->assertEquals('', $f->getData());
        $this->assertEquals(4, $f->getDependsOn());
        $this->assertEquals(64, $f->getStreamWeight());
        $this->assertEquals(true, $f->getExclusive());
        $this->assertEquals(5, $f->getBodyLen());
    }

    public function testHeadersFrameWithPrioritySerializesProperly()
    {
        $s = "\x00\x00\x05\x01\x20\x00\x00\x00\x01\x80\x00\x00\x04\x40";
        $f = new \Hyphper\Frame\HeadersFrame(['stream_id' => 1]);
        $f->setFlags([\Hyphper\Frame\Flag::PRIORITY => \Hyphper\Frame\Flag::PRIORITY]);
        $f->setData('');
        $f->setDependsOn(4);
        $f->setStreamWeight(64);
        $f->setExclusive(true);

        $this->assertEquals($s, $f->serialize());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidPaddingException
     * @expectedExceptionMessage Padding is too long
     */
    public function testHeadersFrameWithInvalidPaddingFailsToParse()
    {
        // This frame has a padding length of 6 bytes, but a total length of 5.
        $data = "\x00\x00\x05\x01\x08\x00\x00\x00\x01\x06\x54\x65\x73\x74";

        $this->decodeFrame($data);
    }

    public function testHeadersFrameWithNoLengthParses()
    {
        // Fixes issue with empty data frames raising InvalidPaddingError.
        $f = new \Hyphper\Frame\HeadersFrame(['stream_id' => 1]);
        $f->setData('');
        $data = $f->serialize();

        $new_frame = $this->decodeFrame($data);
        $this->assertEquals('', $new_frame->getData());
    }

    
}
