<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidPaddingException;

/**
 * The HEADERS frame carries name-value pairs. It is used to open a stream.
 * HEADERS frames can be sent on a stream in the "open" or "half closed
 * (remote)" states.
 *
 * The HeadersFrame class is actually basically a data frame in this
 * implementation, because of the requirement to control the sizes of frames.
 * A header block fragment that doesn't fit in an entire HEADERS frame needs
 * to be followed with CONTINUATION frames. From the perspective of the frame
 * building code the header block is an opaque data segment.
 *
 * @package Hyphper\Frame
 */
class HeadersFrame extends \Hyphper\Frame implements PaddingInterface, PriorityInterface
{
    use PaddingTrait, PriorityTrait;

    protected $defined_flags = [
        Flag::END_STREAM,
        Flag::END_HEADERS,
        Flag::PADDED,
        Flag::PRIORITY
    ];

    protected $type = 0x01;

    protected $stream_association = self::HAS_STREAM;
    protected $data;



    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->data = $options['data'] ?? '';
    }

    public function serializeBody(): string
    {
        $padding_data = $this->serializePaddingData();
        $padding = '';
        if ($this->padding_length) {
            $padding = str_repeat("\0", $this->padding_length);
        }

        $priority_data = '';
        if ($this->flags->hasFlag(Flag::PRIORITY)) {
            $priority_data = $this->serializePriorityData();
        }

        return $padding_data . $priority_data . $this->data . $padding;
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
        $padding_data_length = $this->parsePaddingData($data);
        $data = substr($data, $padding_data_length);

        $priority_data_length = 0;
        if ($this->flags->hasFlag(Flag::PRIORITY)) {
            $priority_data_length = $this->parsePriorityData($data);
        }

        $this->body_len = strlen($data);
        $this->data = substr($data, $priority_data_length, strlen($data) - $this->padding_length);

        if ($this->padding_length && $this->padding_length >= $this->body_len) {
            throw new InvalidPaddingException('Padding is too long');
        }
    }

    /**
     * @param mixed|string $data
     *
     * @return HeadersFrame
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