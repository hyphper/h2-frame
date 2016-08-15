<?php
declare(strict_types=1);
namespace Hyphper\Frame;

trait PaddingTrait
{
    protected $padding_length;

    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->padding_length = (int) ($options['padding_length'] ?? 0);
    }

    public function setPaddingLength($padding_length)
    {
        $this->padding_length = $padding_length;
    }

    /**
     * @return int
     */
    public function getPaddingLength()
    {
        return $this->padding_length;
    }

    protected function serializePaddingData(): string
    {
        if ($this->flags->hasFlag(Flag::PADDED)) {
            return pack('C', $this->padding_length);
        }

        return '';
    }

    protected function parsePaddingData($data): int
    {
        if ($this->flags->hasFlag(Flag::PADDED)) {
            if (!$unpack = @unpack('Cpadding_length', substr($data, 0, 1))) {
                throw new \Hyphper\Frame\Exception\InvalidFrameException("Invalid Padding Data");
            }

            $this->padding_length = $unpack['padding_length'];

            return 1;
        }

        return 0;
    }
}