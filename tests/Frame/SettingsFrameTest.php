<?php
namespace Hyphper\Test;

class SettingsFrameTest extends FrameTest
{
    protected $serialized =  "\x00\x00\x24\x04\x01\x00\x00\x00\x00" .   // Frame header
                             "\x00\x01\x00\x00\x10\x00" .               // HEADER_TABLE_SIZE
                             "\x00\x02\x00\x00\x00\x00" .               // ENABLE_PUSH
                             "\x00\x03\x00\x00\x00\x64" .               // MAX_CONCURRENT_STREAMS
                             "\x00\x04\x00\x00\xFF\xFF" .               // INITIAL_WINDOW_SIZE
                             "\x00\x05\x00\x00\x40\x00" .               // MAX_FRAME_SIZE
                             "\x00\x06\x00\x00\xFF\xFF";                // MAX_HEADER_LIST_SIZE

    protected $settings = [
        \Hyphper\Frame\SettingsFrame::HEADER_TABLE_SIZE => 4096,
        \Hyphper\Frame\SettingsFrame::ENABLE_PUSH => 0,
        \Hyphper\Frame\SettingsFrame::MAX_CONCURRENT_STREAMS => 100,
        \Hyphper\Frame\SettingsFrame::INITIAL_WINDOW_SIZE => 65535,
        \Hyphper\Frame\SettingsFrame::MAX_FRAME_SIZE => 16384,
        \Hyphper\Frame\SettingsFrame::MAX_HEADER_LIST_SIZE => 65535
    ];

    public function testSettingsFrameHasOnlyOneFlag()
    {
        $f = new \Hyphper\Frame\SettingsFrame();
        $flags = $f->parseFlags(0xFF);
        $this->assertEquals([\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK], $flags->getIterator());
    }

    public function testSettingsFrameSerializesProperly()
    {
        $f = new \Hyphper\Frame\SettingsFrame();
        $f->parseFlags(0xFF);
        $f->setSettings($this->settings);

        $s = $f->serialize();
        $this->assertEquals($this->serialized, $s);
    }

    public function testSettingsFrameWithSettings()
    {
        $f = new \Hyphper\Frame\SettingsFrame(['settings' => $this->settings]);
        $this->assertEquals($this->settings, $f->getSettings());
    }

    public function testSettingsFrameWithoutSettings()
    {
        $f = new \Hyphper\Frame\SettingsFrame();
        $this->assertEquals([], $f->getSettings());
    }

    public function testSettingsFrameWithAck()
    {
        $f = new \Hyphper\Frame\SettingsFrame(['flags' => [\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK]]);
        $this->assertTrue($f->getFlags()->hasFlag(\Hyphper\Frame\Flag::ACK));
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Settings must be empty if ACK flag is set.
     */
    public function testSettingsFrameAckAndSettings()
    {
        new \Hyphper\Frame\SettingsFrame([
            'settings' => $this->settings,
            'flags' => [\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK]
        ]);
    }

    public function testSettingsFrameParsesProperly()
    {
        $f = $this->decodeFrame($this->serialized);

        $this->assertInstanceOf(\Hyphper\Frame\SettingsFrame::class, $f);
        $this->assertEquals([\Hyphper\Frame\Flag::ACK => \Hyphper\Frame\Flag::ACK], $f->getFlags()->getIterator());
        $this->assertEquals($this->settings, $f->getSettings());
        $this->assertEquals(36, $f->getBodyLen());
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     */
    public function testSettingsFramesNeverHaveStreams()
    {
        new \Hyphper\Frame\SettingsFrame(['stream_id' => 1]);
    }

    /**
     * @expectedException \Hyphper\Frame\Exception\InvalidFrameException
     * @expectedExceptionMessage Invalid SETTINGS body
     */
    public function testShortSettingsFrameErrors()
    {
        $this->decodeFrame(substr($this->serialized, 0, -2));
    }
}
