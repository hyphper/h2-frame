<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;

/**
 * The RST_STREAM frame allows for abnormal termination of a stream. When sent
 * by the initiator of a stream, it indicates that they wish to cancel the
 * stream or that an error condition has occurred. When sent by the receiver
 * of a stream, it indicates that either the receiver is rejecting the stream,
 * requesting that the stream be cancelled or that an error condition has
 * occurred.
 *
 * @package Hyphper\Frame
 */
class RstStreamFrame extends \Hyphper\Frame
{
    protected $defined_flags = [];
    protected $type = 0x03;
    protected $stream_association = self::HAS_STREAM;
    protected $error_code;

    /**
     * RstStreamFrame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->error_code = (int) ($options['error_code'] ?? null);
    }

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        return pack('N', $this->error_code);
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
        if (strlen($data) != 4) {
            throw new InvalidFrameException(sprintf(
                "RST_STREAM must have 4 byte body: actual length %s.",
                strlen($data)
            ));
        }

        if (!$unpack = @unpack('Nerror_code', $data)) {
            throw new InvalidFrameException('Invalid RST_STREAM body');
        }

        $this->error_code = $unpack['error_code'];
        $this->body_len = strlen($data);
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param int $error_code
     * @return RstStreamFrame
     */
    public function setErrorCode($error_code)
    {
        $this->error_code = $error_code;
        return $this;
    }
}
