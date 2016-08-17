<?php
declare(strict_types=1);
namespace Hyphper\Frame;

use Hyphper\Frame\Exception\InvalidFrameException;

/**
 * The WINDOW_UPDATE frame is used to implement flow control.
 * Flow control operates at two levels: on each individual stream and on the
 * entire connection.
 *
 * Both types of flow control are hop by hop; that is, only between the two
 * endpoints. Intermediaries do not forward WINDOW_UPDATE frames between
 * dependent connections. However, throttling of data transfer by any receiver
 * can indirectly cause the propagation of flow control information toward the
 * original sender.
 *
 * @package Hyphper\Frame
 */
class WindowUpdateFrame extends \Hyphper\Frame
{
    protected $defined_flags = [];
    protected $type = 0x08;
    protected $stream_association = self::EITHER_STREAM;
    protected $window_increment;

    /**
     * WindowUpdateFrame constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->window_increment = $options['window_increment'] ?? 0;
    }

    /**
     * @return string
     */
    public function serializeBody(): string
    {
        return pack('N', $this->window_increment & 0x7FFFFFFF);
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
        if (!$unpack = @unpack('Nwindow_increment', $data)) {
            throw new InvalidFrameException('Invalid WINDOW_UPDATE body');
        }

        $this->window_increment = $unpack['window_increment'];
        $this->body_len = strlen($data);
    }

    /**
     * @param int|mixed $window_increment
     *
     * @return WindowUpdateFrame
     */
    public function setWindowIncrement($window_increment)
    {
        $this->window_increment = $window_increment;

        return $this;
    }

    /**
     * @return int|mixed
     */
    public function getWindowIncrement()
    {
        return $this->window_increment;
    }
}
