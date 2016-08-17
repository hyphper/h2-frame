<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;
use Hyphper\Frame\Exception\InvalidPaddingException;

/**
 * The PUSH_PROMISE frame is used to notify the peer endpoint in advance of
 * streams the sender intends to initiate.
 *
 * @package Hyphper\Frame
 */
class PushPromiseFrame extends \Hyphper\Frame implements PaddingInterface
{
    use PaddingTrait;

    protected $defined_flags = [
        Flag::END_HEADERS,
        Flag::PADDED
    ];

    protected $type = 0x05;
    protected $stream_association = self::HAS_STREAM;
    /**
     * @var int The stream ID that is promised by this frame.
     */
    protected $promised_stream_id;

    protected $data;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->promised_stream_id = (int) ($options['promised_stream_id'] ?? null);
        $this->data = $options['data'] ?? 0;
    }

    public function serializeBody(): string
    {
        $padding_data = $this->serializePaddingData();
        $padding = '';
        if ($this->padding_length) {
            $padding = str_repeat("\0", $this->padding_length);
        }
        $data = pack('N', $this->promised_stream_id);

        return $padding_data . $data . $this->data . $padding;
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     * @throws InvalidFrameException
     * @throws InvalidPaddingException
     * @return void
     */
    public function parseBody(string $data)
    {
        $padding_data_length = $this->parsePaddingData($data);

        if (!$unpack = @unpack('Npromised_stream_id', substr($data, $padding_data_length, $padding_data_length + 4))) {
            throw new InvalidFrameException('Invalid PUSH_PROMISE body');
        }

        $this->promised_stream_id = $unpack['promised_stream_id'];

        $this->data = substr($data, $padding_data_length + 4);
        $this->body_len = strlen($data);

        if ($this->padding_length && $this->padding_length > $this->body_len) {
            throw new InvalidPaddingException('Padding is too long');
        }
    }

    /**
     * @return int
     */
    public function getPromisedStreamId()
    {
        return $this->promised_stream_id;
    }

    /**
     * @param int $promised_stream_id
     * @return $this
     */
    public function setPromisedStreamId(int $promised_stream_id)
    {
        $this->promised_stream_id = $promised_stream_id;
        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setData(string $data)
    {
        $this->data = $data;
        return $this;
    }
}