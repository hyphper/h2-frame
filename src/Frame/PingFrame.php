<?php
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;

/**
 * The PING frame is a mechanism for measuring a minimal round-trip time from
 * the sender, as well as determining whether an idle connection is still
 * functional. PING frames can be sent from any endpoint.
 *
 * @package Hyphper\Frame
 */
class PingFrame extends \Hyphper\Frame
{
    protected $defined_flags = [Flag::ACK];
    protected $type = 0x06;
    protected $stream_association = self::NO_STREAM;
    protected $opaque_data;

    /**
     * PingFrame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->opaque_data = $options['opaque_data'] ?? '';
    }

    /**
     * @return string
     * @throws InvalidFrameException
     */
    public function serializeBody(): string
    {
        if (strlen($this->opaque_data) > 8) {
            throw new InvalidFrameException(
                sprintf('PING frame may not have more than 8 bytes of data, got %d', strlen($this->opaque_data))
            );
        }

        $data = $this->opaque_data;
        $data = str_pad($data, 8, "\x00", STR_PAD_RIGHT);

        return $data;
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     *
     * @return void
     */
    public function parseBody(string $data)
    {
        if (strlen($data) != 8) {
            throw new InvalidFrameException(
                sprintf('PING frame must have 8 byte length, got %d', strlen($data))
            );
        }

        $this->opaque_data = $data;
        $this->body_len = strlen($data);
    }

    /**
     * @return string
     */
    public function getOpaqueData(): string
    {
        return $this->opaque_data;
    }

    /**
     * @param string $opaque_data
     * @return $this
     */
    public function setOpaqueData(string $opaque_data)
    {
        $this->opaque_data = $opaque_data;
        return $this;
    }
}
