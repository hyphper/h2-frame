<?php
declare(strict_types=1);
namespace Hyphper\Frame;

/**
 * The PRIORITY frame specifies the sender-advised priority of a stream. It
 * can be sent at any time for an existing stream. This enables
 * reprioritisation of existing streams.
 *
 * @package Hyphper\Frame
 */
class PriorityFrame extends \Hyphper\Frame implements PriorityInterface
{
    use PriorityTrait;

    protected $defined_flags = [];
    protected $type = 0x02;
    protected $stream_association = self::HAS_STREAM;

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        return $this->serializePriorityData();
    }

    /**
     * Given the body of a frame, parses it into frame data. This populates
     * the non-header parts of the frame: that is, it does not populate the
     * stream ID or flags.
     *
     * @param string $data
     *
     * @return void
     */
    public function parseBody(string $data)
    {
        $this->parsePriorityData($data);
        $this->body_len = strlen($data);
    }
}
