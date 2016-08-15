<?php
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;

/**
 * The SETTINGS frame conveys configuration parameters that affect how
 * endpoints communicate. The parameters are either constraints on peer
 * behavior or preferences.
 *
 * Settings are not negotiated. Settings describe characteristics of the
 * sending peer, which are used by the receiving peer. Different values for
 * the same setting can be advertised by each peer. For example, a client
 * might set a high initial flow control window, whereas a server might set a
 * lower value to conserve resources.
 *
 * @package Hyphper\Frame
 */
class SettingsFrame extends \Hyphper\Frame
{
    protected $defined_flags = [Flag::ACK];
    protected $type = 0x04;
    protected $stream_association = self::NO_STREAM;
    protected $settings;

    /**
     * The byte that signals the SETTINGS_HEADER_TABLE_SIZE setting.
     */
    const HEADER_TABLE_SIZE = 0x01;

    /**
     * The byte that signals the SETTINGS_ENABLE_PUSH setting.
     */
    const ENABLE_PUSH = 0x02;
    /**
     * The byte that signals the SETTINGS_MAX_CONCURRENT_STREAMS setting.
     */
    const MAX_CONCURRENT_STREAMS = 0x03;

    /**
     * The byte that signals the SETTINGS_INITIAL_WINDOW_SIZE setting.
     */
    const INITIAL_WINDOW_SIZE = 0x04;

    /**
     * The byte that signals the SETTINGS_MAX_FRAME_SIZE setting.
     */
    const MAX_FRAME_SIZE = 0x05;

    /**
     * The byte that signals the SETTINGS_MAX_HEADER_LIST_SIZE setting.
     */
    const MAX_HEADER_LIST_SIZE = 0x06;

    /**
     * SettingsFrame constructor.
     *
     * @param array $settings
     * @param array ...$args
     * @throws InvalidFrameException
     */
    public function __construct(array $options = []) // array $settings = [], ... $args)
    {
        parent::__construct($options);

        $options['settings'] = $options['settings'] ?? [];

        if ($options['settings'] && $this->flags->hasFlag(Flag::ACK)) {
            throw new InvalidFrameException('Settings must be empty if ACK flag is set.');
        }

        $this->settings = $options['settings'];
    }

    public function serializeBody(): string
    {
        $settings = [];
        foreach ($this->settings as $setting => $value) {
            $settings[] = pack('nN', $setting & 0xFF, $value);
        }

        return implode('', $settings);
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     * @throws InvalidFrameException
     * @return string
     */
    public function parseBody(string $data): string
    {
        foreach (range(0, strlen($data) - 1, 6) as $i) {
            if (!$unpack = @unpack('nname/Nvalue', substr($data, $i, $i+6))) {
                throw new InvalidFrameException('Invalid SETTINGS body');
            }

            $name = $unpack['name'];
            $value = $unpack['value'];
            $this->settings[$name] = $value;
        }

        $this->body_len = strlen($data);

        return $data;
    }

    /**
     * @return array|mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array|mixed $settings
     * @return SettingsFrame
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }
}