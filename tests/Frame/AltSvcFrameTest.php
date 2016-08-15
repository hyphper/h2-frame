<?php
namespace Hyphper\Test;

class AltSvcFrameTest extends FrameTest
{
    protected $payload_with_origin =
        "\x00\x00\x31" .                             // Length
        "\x0A" .                                     // Type
        "\x00" .                                     // Flags
        "\x00\x00\x00\x00" .                         // Stream ID
        "\x00\x0B" .                                 // Origin len
        "example.com" .                              // Origin
        "h2=\"alt.example.com:8000\", h2=\":443\"";  // Field Value

    protected $payload_without_origin =
        "\x00\x00\x13" .  # Length
        "\x0A" .  # Type
        "\x00" .  # Flags
        "\x00\x00\x00\x01" .  # Stream ID
        "\x00\x00" .  # Origin len
        "" .  # Origin
        "h2=\":8000\"; ma=60";  # Field Value

    protected $payload_with_origin_and_stream =
        "\x00\x00\x36" .                                  // Length
        "\x0A" .                                          // Type
        "\x00" .                                          // Flags
        "\x00\x00\x00\x01" .                              // Stream ID
        "\x00\x0B" .                                      // Origin len
        "example.com" .                                   // Origin
        "Alt-Svc: h2=\":443\"; ma=2592000; persist=1";  // Field Value

    public function testAltSvcFrameFlags()
    {
        $f = new \Hyphper\Frame\AltSvcFrame(['stream_id' => 0]);
        $flags = $f->parseFlags(0xFF);

        $this->assertEquals([], $flags->getIterator());
    }

    public function testAllSvcFrameWithOriginSerializesProperly()
    {
        $f = new \Hyphper\Frame\AltSvcFrame(['stream_id' => 0]);
        $f->setOrigin('example.com');
        $f->setField('h2="alt.example.com:8000", h2=":443"');

        $s = $f->serialize();
        $this->assertEquals($this->payload_with_origin, $s);
    }

    public function testAltSvcFrameWithOriginParsesProperly()
    {
        $f = $this->decodeFrame($this->payload_with_origin);

        $this->assertInstanceOf(\Hyphper\Frame\AltSvcFrame::class, $f);
        $this->assertEquals('example.com', $f->getOrigin());
        $this->assertEquals('h2="alt.example.com:8000", h2=":443"', $f->getField());
        $this->assertEquals(49, $f->getBodyLen());
        $this->assertEquals(0, $f->getStreamId());
    }

    public function testAltSvcFrameWithoutOriginSerializesProperly()
    {
        $f = new \Hyphper\Frame\AltSvcFrame([
            'stream_id' => 1,
            'field' => 'h2=":8000"; ma=60'
        ]);

        $s = $f->serialize();
        $this->assertEquals($this->payload_without_origin, $s);
    }

    public function testAltSvcFrameWithoutOriginParsesProperly()
    {
        $f = $this->decodeFrame($this->payload_without_origin);

        $e = $f->__debugInfo()[0];

        $this->assertEquals("AltSvcFrame(Stream: 1; Flags: None): 000068323d223a383030...", $e);
    }

    public function testAltSvcFrameWithOriginAndStreamSerializesProperly()
    {
        $f = new \Hyphper\Frame\AltSvcFrame(['stream_id' => 1]);
        $f->setOrigin('example.com');
        $f->setField('Alt-Svc: h2=":443"; ma=2592000; persist=1');

        $this->assertEquals($this->payload_with_origin_and_stream, $f->serialize());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\UnknownFrameException
     */
    public function testShortAltSvcFrameErrors()
    {
        $this->decodeFrame(substr($this->payload_with_origin, 12));
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\UnknownFrameException
     */
    public function testShortAltSvcFrameErrors2()
    {
        $this->decodeFrame(substr($this->payload_with_origin, 10));
    }
}
