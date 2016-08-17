<?php
namespace Hyphper\Frame\Exception;

/**
 * A frame of unknown type was received.
 *
 * @package Hyphper\Frame
 */
class UnknownFrameException extends \Exception
{
    protected $frame_type;
    protected $length;

    /**
     * UnknownFrameException constructor.
     *
     * @param string $frame_type
     * @param int $length
     */
    public function __construct($frame_type, $length)
    {
        $this->frame_type = $frame_type;
        $this->length = $length;

        parent::__construct(
            sprintf(
                "UnknownFrameError: Unknown frame type 0x%X received, length %d bytes",
                $frame_type,
                $length
            )
        );
    }

    /**
     * @return string
     */
    public function getFrameType()
    {
        return $this->frame_type;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
}
