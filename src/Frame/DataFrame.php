<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\PaddingInterface;

/**
 * DATA frames convey arbitrary, variable-length sequences of octets
 * associated with a stream. One or more DATA frames are used, for instance,
 * to carry HTTP request or response payloads.
 *
 * @package Hyphper\Frame
 */
class DataFrame extends \Hyphper\Frame implements PaddingInterface
{
    use PaddingTrait;

    protected $defined_flags = [
        Flag::END_STREAM,
        Flag::PADDED
    ];

    protected $type = 0x0;
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
        $padding = str_repeat("\0", $this->padding_length ?? 0);

        return $padding_data . $this->data . $padding;
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     *
     * @return string
     */
    public function parseBody(string $data): string
    {
        $padding_data_length = $this->parsePaddingData($data);
        $this->data = substr($data, $padding_data_length, ($this->padding_length) ? $this->padding_length * -1 : strlen($data));

        $this->body_len = strlen($data);
        if ($this->padding_length && $this->padding_length >= $this->body_len) {
            throw new \Hyphper\Frame\Exception\InvalidPaddingException("Padding is too long.");
        }

        return $this->data;
    }

    /**
     * The length of the frame that needs to be accounted for when considering
     * flow control.
     */
    public function flowControlledLength()
    {
        $padding_len = ($this->padding_length) ? $this->padding_length + 1 : 0;
        return strlen($this->data) + $padding_len;
    }

    /**
     * @return int
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param int $data
     *
     * @return DataFrame
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}