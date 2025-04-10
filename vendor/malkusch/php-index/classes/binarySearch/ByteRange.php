<?php

namespace malkusch\index;

/**
 * Range for the binary search
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class ByteRange
{

    /**
     * @var int
     */
    private $offset = 0;
    /**
     * @var int
     */
    private $length = 0;

    /**
     * Sets the range
     *
     * @param int $offset Offset
     * @param int $length Length
     */
    public function __construct($offset, $length)
    {
        $this->offset = $offset;
        $this->length = $length;
    }
    
    /**
     * Sets the offset
     *
     * @param int $offset Offset
     *
     * @return void
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * Sets a new length
     *
     * @param int $length Length
     *
     * @return void
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Returns the beginning of the range
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns the length of the range
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }
    
    /**
     * Returns the offset of the last byte + 1 of this range
     * 
     * @return int
     */
    public function getNextByteOffset()
    {
        return $this->offset + $this->length;
    }
}
