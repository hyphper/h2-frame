<?php
declare(strict_types=1);
namespace Hyphper\Frame;

/**
 * The CONTINUATION frame is used to continue a sequence of header block
 * fragments. Any number of CONTINUATION frames can be sent on an existing
 * stream, as long as the preceding frame on the same stream is one of
 * HEADERS, PUSH_PROMISE or CONTINUATION without the END_HEADERS flag set.
 * Much like the HEADERS frame, hyper treats this as an opaque data frame with
 * different flags and a different type.
 *
 * @package Hyphper\Frame
 */
class ContinuationFrame extends \Hyphper\Frame
{
    protected $defined_flags = [Flag::END_HEADERS];
    protected $type = 0x09;
    protected $stream_association = self::HAS_STREAM;
    protected $data;

    /**
     * ContinuationFrame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->data = $options['data'] ?? '';
    }

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        return $this->data;
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
        $this->data = $data;
        $this->body_len = strlen($data);
    }

    /**
     * @param mixed|string $data
     *
     * @return ContinuationFrame
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getData()
    {
        return $this->data;
    }
}