<?php
/**
 * Defines framing logic for HTTP/2. Provides both classes to represent framed
 * data and logic for aiding the connection when it comes to reading from the
 * socket.
 *
 * @package Hyphper
 */
declare(strict_types=1);

namespace Hyphper;

use Hyphper\Frame\Flags;

use Hyphper\Frame\Exception\InvalidFrameException;
use Hyphper\Frame\Exception\UnknownFrameException;

/**
 * The base class for all HTTP/2 frames.
 *
 * @package Hyphper
 */
abstract class Frame
{
    const FRAMES = [
        0x0 => 'DataFrame',
        0x01 => 'HeadersFrame',
        0x02 => 'PriorityFrame',
        0x03 => 'RstStreamFrame',
        0x04 => 'SettingsFrame',
        0x05 => 'PushPromiseFrame',
        0x06 => 'PingFrame',
        0x07 => 'GoAwayFrame',
        0x08 => 'WindowUpdateFrame',
        0x09 => 'ContinuationFrame',
        0xA => 'AltSvcFrame',
    ];

    const HAS_STREAM = 1;
    const NO_STREAM = 2;
    const EITHER_STREAM = 4;
    const BOTH_STREAM = 8;

    /**
     * @var array The flags defined on this type of frame.
     */
    protected $defined_flags = [];

    /**
     * @var Flags
     */
    protected $flags;

    /**
     * @var int The byte used to define the type of the frame.
     */
    protected $type;

    /**
     * @var int The length of the frame
     */
    protected $length;

    /**
     * If true, the frame's stream_id must be non-zero. If false,
     * it must be zero. If null, stream_id is ignored.
     *
     * @var int
     */
    protected $stream_association;

    /**
     * @var int
     */
    protected $stream_id = 0;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var int
     */
    protected $body_len = 0;

    /**
     * Frame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->stream_id = $options['stream_id'] ?? 0;
        $this->length = $options['length'] ?? 0;

        $this->flags = new Flags(... $this->defined_flags);

        foreach ($options['flags'] ?? [] as $flag) {
            $this->flags->add($flag);
        }

        if ($this->stream_association !== self::EITHER_STREAM && $this->stream_association !== self::BOTH_STREAM) {
            if ($this->stream_association !== self::NO_STREAM && !$this->stream_id) {
                throw new InvalidFrameException();
            }

            if ($this->stream_association === self::NO_STREAM && $this->stream_id) {
                throw new InvalidFrameException();
            }
        }
    }

    /**
     * @param $data
     *
     * @return Frame
     * @throws InvalidFrameException
     * @throws UnknownFrameException
     */
    public static function parseFrame($data): Frame
    {
        $frame = static::parseFrameHeader(substr($data, 0, 9));
        $length = $frame->getLength();

        $frame->parseBody(strlen($data, 9, $length));

        return $frame;
    }

    /**
     * Takes a 9-byte frame header and returns a tuple of the appropriate
     * Frame object and the length that needs to be read from the socket.
     * This populates the flags field, and determines how long the body is.
     *
     * @param string $header
     * @throws UnknownFrameException If a frame of unknown type is received.
     */
    public static function parseFrameHeader(string $header): Frame
    {
        /*
         * H = unsigned 16bit int = n
         * B = unsigned 8bit string = C
         * L = unsigned 32bit int = N
         */

        if (!$fields = @unpack('nlength8/Clength16/Ctype/Cflags/Nstream_id', $header)) {
            throw new InvalidFrameException("Invalid Frame Header");
        }

        $length = ($fields['length8'] << 8) + $fields['length16'];
        $type = $fields['type'];
        $flags = $fields['flags'];
        $stream_id = $fields['stream_id'];

        if (!array_key_exists($type, static::FRAMES)) {
            throw new UnknownFrameException($type, $length);
        }

        $frame = '\Hyphper\Frame\\' . static::FRAMES[$type];
        $frame = new $frame(['stream_id' => $stream_id, 'length' => $length]);
        $frame->parseFlags($flags);

        return $frame;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Convert a frame into a bytestring, representing the serialized form of
     * the frame.
     *
     * @return string
     */
    public function serialize():string
    {
        $body = $this->serializeBody();
        $this->body_len = strlen($body);

        $flags = 0;

        foreach ($this->defined_flags as $flag) {
            if ($this->flags->hasFlag($flag)) {
                $flags |= $flag;
            }
        }

        $header = pack(
            'nCCCN',
            ($this->body_len & 0xFFFF00) >> 8,    // Length spread over top 24 bits
            $this->body_len & 0x0000FF,
            $this->type,
            $flags,
            $this->stream_id & 0x7FFFFFFF   // Stream ID is 32 bits.
        );

        return $header . $body;
    }

    /**
     * @param $flag_byte
     *
     * @return Flags
     * @throws Frame\Exception\InvalidFlagException
     */
    public function parseFlags(int $flag_byte): Flags
    {
        foreach ($this->defined_flags as $flag) {
            if ($flag_byte & $flag) {
                $this->flags->add($flag);
            }
        }

        return $this->flags;
    }

    abstract public function serializeBody(): string;

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     *
     * @param string $data
     * @return void
     */
    abstract public function parseBody(string $data);

    /**
     * @return Flags
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param array $flags
     * @return $this
     */
    public function setFlags(array $flags)
    {
        foreach ($flags as $flag) {
            if (in_array($flag, $this->defined_flags)) {
                $this->flags->add($flag);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getBodyLen()
    {
        return $this->body_len;
    }

    /**
     * @param int $stream_id
     *
     * @return Frame
     */
    public function setStreamId($stream_id)
    {
        $this->stream_id = $stream_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getStreamId()
    {
        return $this->stream_id;
    }

    public function __debugInfo()
    {
        $flags = "None";
        if ($f = $this->flags->getIterator()) {
            $flags = implode(", ", $f);
        }

        $body = bin2hex($this->serializeBody());
        if (strlen($body) > 20) {
            $body = substr($body, 0, 20) . '...';
        }

        return [sprintf(
            "%s(Stream: %s; Flags: %s): %s",
            substr(strrchr(static::class, '\\'), 1),
            $this->stream_id ?? "None",
            $flags,
            $body
        )];
    }
}
