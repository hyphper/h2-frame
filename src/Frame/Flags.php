<?php
declare(strict_types = 1);
namespace Hyphper\Frame;

class Flags implements \IteratorAggregate
{
    protected $valid_flags = [];
    protected $flags = [];

    public function __construct(int ...$valid_flags)
    {
        $this->valid_flags = array_combine($valid_flags, $valid_flags);
    }

    public function getIterator(): array
    {
        return $this->flags;
    }

    public function add($flag)
    {
        if (isset($this->valid_flags[$flag])) {
            return $this->flags[$flag] = $flag;
        }

        throw new \Hyphper\Frame\Exception\InvalidFlagException($flag, $this->valid_flags);
    }

    public function hasFlag($flag)
    {
        return isset($this->flags[$flag]);
    }
}
