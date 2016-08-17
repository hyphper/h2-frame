<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;

/**
 * The ALTSVC frame is used to advertise alternate services that the current
 * host, or a different one, can understand. This frame is standardised as
 * part of RFC 7838.
 *
 * This frame does no work to validate that the ALTSVC field parameter is
 * acceptable per the rules of RFC 7838.
 *
 * note: If the stream_id of this frame is nonzero, the origin field
 * must have zero length. Conversely, if the stream_id of this
 * frame is zero, the origin field must have nonzero length. Put
 * another way, a valid ALTSVC frame has stream_id != 0 XOR
 * strlen(origin) != 0.
 *
 * @package Hyphper\Frame
 */
class AltSvcFrame extends \Hyphper\Frame
{
    protected $defined_flags = [];
    protected $type = 0xA;
    protected $stream_association = self::BOTH_STREAM;
    protected $origin;
    protected $field;

    /**
     * AltSvcFrame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->origin = $options['origin'] ?? '';
        $this->field = $options['field'] ?? '';
    }

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        $origin_len = pack('n', strlen($this->origin));
        return $origin_len . $this->origin . $this->field;
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     * @return void
     */
    public function parseBody(string $data)
    {
        if (!$unpack = @unpack('norigin_length', substr($data, 0, 2))) {
            throw new InvalidFrameException('Invalid ALTSVC frame body.');
        }
        $origin_length = $unpack['origin_length'];

        $this->origin = substr($data, 2, $origin_length);

        if (strlen($this->origin) != $origin_length) {
            throw new InvalidFrameException('Invalid ALTSVC frame body.');
        }

        $this->field = substr($data, 2 + $origin_length);

        $this->body_len = strlen($data);
    }

    /**
     * @param mixed|string $origin
     *
     * @return AltSvcFrame
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed|string $field
     *
     * @return AltSvcFrame
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getField()
    {
        return $this->field;
    }
}