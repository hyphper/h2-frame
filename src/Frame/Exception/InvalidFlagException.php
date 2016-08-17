<?php
namespace Hyphper\Frame\Exception;

class InvalidFlagException extends \Exception
{
    /**
     * InvalidFlagException constructor.
     *
     * @param string $flag
     * @param int $valid_flags
     */
    public function __construct($flag, $valid_flags)
    {
        parent::__construct(
            sprintf(
                "Unexpected flag: 0x%02x. Valid flags are: %s",
                $flag,
                ($valid_flags) ? $this->getValidFlags($valid_flags) : "none"
            )
        );
    }

    /**
     * @param $valid_flags
     *
     * @return string
     */
    protected function getValidFlags($valid_flags)
    {
        $flags = [];
        foreach ($valid_flags as $flag) {
            $flags[] = sprintf('0x%02x', $flag);
        }

        return implode(', ', $flags);
    }
}
