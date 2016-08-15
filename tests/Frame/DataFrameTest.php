<?php
namespace Hyphper\Test;

class DataFrameTest extends FrameTest
{
    protected $payload;
    protected $payload_with_padding;

    public function setUp()
    {
        parent::setUp();

        $this->payload = "\x00\x00\x08\x00\x01\x00\x00\x00\x01testdata";
        $this->payload_with_padding = "\x00\x00\x13\x00\x09\x00\x00\x00\x01\x0Atestdata" . str_repeat("\0", 10);
    }
    
    public function testDataFrameHasCorrectFlags()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $flags = $f->parseFlags(0xFF);
        $this->assertEquals([
            \Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM,
            \Hyphper\Frame\Flag::PADDED => \Hyphper\Frame\Flag::PADDED
        ], $flags->getIterator());
    }

    public function testDataFrameParsesProperly()
    {
        $f = $this->decodeFrame($this->payload);
        $this->assertInstanceOf(\Hyphper\Frame\DataFrame::class, $f);

        $this->assertEquals([\Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM],
            $f->getFlags()->getIterator());
        $this->assertEquals(0, $f->getPaddingLength());
        $this->assertEquals('testdata', $f->getData());
        $this->assertEquals(8, $f->getBodyLen());
    }

    public function testDataFrameWithPaddingParsesProperly()
    {
        $f = $this->decodeFrame($this->payload_with_padding);
        $this->assertInstanceOf(\Hyphper\Frame\DataFrame::class, $f);
        $this->assertEquals([
            \Hyphper\Frame\Flag::END_STREAM => \Hyphper\Frame\Flag::END_STREAM,
            \Hyphper\Frame\Flag::PADDED => \Hyphper\Frame\Flag::PADDED
        ], $f->getFlags()->getIterator());

        $this->assertEquals(10, $f->getPaddingLength());
        $this->assertEquals('testdata', $f->getData());
        $this->assertEquals(19, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testDataFrameWithInvalidPaddingErrors()
    {
        $this->decodeFrame(substr($this->payload_with_padding, 0, 9));
    }
    
    public function testWithPaddingCalculatesFlowControlLen()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $f->getFlags()->add(\Hyphper\Frame\Flag::PADDED);
        $f->setData('testdata');
        $f->setPaddingLength(10);
        $this->assertEquals(19, $f->flowControlledLength());
    }

    public function testDataFrameWithoutPaddingCalculatesFlowControlLen()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $f->setData('testdata');
        $this->assertEquals(8, $f->flowControlledLength());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testDataFrameComesOnAStream()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 0]);
    }

    public function testLongDataFrame()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $f->setData(str_repeat("\x01", 300));
        $data = $f->serialize();

        $this->assertEquals("\x00\x01\x2C", substr($data, 0, 3));
    }

    public function testBodyLengthBehavesCorrectly()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $f->setData(str_repeat("\x01", 300));
        $this->assertEquals(0, $f->getBodyLen());

        $f->serialize();
        $this->assertEquals(300, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidPaddingException
     */
    public function testDataFrameWithInvalidPaddingFailsToParse()
    {
        $data = "\x00\x00\x05\x00\x0b\x00\x00\x00\x01\x06\x54\x65\x73\x74";

        $this->decodeFrame($data);
    }

    public function testDataFrameWithNoLengthParses()
    {
        $f = new \Hyphper\Frame\DataFrame(['stream_id' => 1]);
        $f->setData('');
        $data = $f->serialize();

        $new_frame = $this->decodeFrame($data);
        $this->assertEquals('', $new_frame->getData());
    }

    public function testDataFrameSerialize()
    {
        $f = $this->decodeFrame($this->payload);
        $this->assertEquals($this->payload, $f->serialize());
    }

    public function testDataFrameSerializeWithPadding()
    {
        $f = $this->decodeFrame($this->payload_with_padding);
        $this->assertEquals($this->payload_with_padding, $f->serialize());
    }
}
