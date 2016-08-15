<?php
namespace Hyphper\Test;

class PushPromiseFrameTest extends FrameTest
{
    public function testPushPromiseFrameFlags()
    {
        $f = new \Hyphper\Frame\PushPromiseFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);
        $this->assertEquals(
            [
                \Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS,
                \Hyphper\Frame\Flag::PADDED => \Hyphper\Frame\Flag::PADDED
            ],
            $flags->getIterator()
        );
    }
    
    public function testPushPromiseFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\PushPromiseFrame(['stream_id' => 1]);
        $f->setFlags([\Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS]);
        $f->setPromisedStreamId(4);
        $f->setData('hello world');

        $s = $f->serialize();
        $this->assertEquals(
            "\x00\x00\x0F\x05\x04\x00\x00\x00\x01" .
            "\x00\x00\x00\x04" .
            "hello world",
            $s
        );
    }

    public function testPushPromiseFrameParsesProperly()
    {
        $f = $this->decodeFrame(
            "\x00\x00\x0F\x05\x04\x00\x00\x00\x01" .
            "\x00\x00\x00\x04" .
            "hello world"
        );

        $this->assertInstanceOf(\Hyphper\Frame\PushPromiseFrame::class, $f);
        $this->assertEquals(
            [\Hyphper\Frame\Flag::END_HEADERS => \Hyphper\Frame\Flag::END_HEADERS],
            $f->getFlags()->getIterator()
        );
        $this->assertEquals(4, $f->getPromisedStreamId());
        $this->assertEquals('hello world', $f->getData());
        $this->assertEquals(15, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidPaddingException
     * @expectedExceptionMessage Padding is too long
     */
    public function testPushPromiseFrameWithInvalidPaddingFailsToParse()
    {
        // This frame has a padding length of 6 bytes, but a total length of 5
        $data = "\x00\x00\x05\x05\x08\x00\x00\x00\x01\x06\x54\x65\x73\x74";
        $this->decodeFrame($data);
    }

    public function testPushPromiseFrameWithNoLengthParses()
    {
        $f = new \Hyphper\Frame\PushPromiseFrame(['stream_id' => 1]);
        $f->setData('');
        $data = $f->serialize();

        $new_frame = $this->decodeFrame($data);
        $this->assertEquals('', $new_frame->getData());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Invalid PUSH_PROMISE body
     */
    public function testShortPushPromiseErrors()
    {
        $s = "\x00\x00\x0F\x05\x04\x00\x00\x00\x01\x00\x00\x00";
        $this->decodeFrame($s);
    }
}
