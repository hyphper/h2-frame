<?php
declare(strict_types = 1);
namespace Hyphper\Frame;

class Flags implements \IteratorAggregate, \Countable
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

    public function remove($flag)
    {
        if (isset($this->flags[$flag])) {
            unset($this->flags[$flag]);
        }
    }

    public function hasFlag($flag)
    {
        return isset($this->flags[$flag]);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->flags);
    }
}
