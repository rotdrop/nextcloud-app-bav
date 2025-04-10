<?php

namespace malkusch\index;

/**
 * Iterator for Result Objects
 * 
 * This iterator returns Result objects.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
interface ResultIterator extends \Iterator
{
    
    /**
     * @return Result 
     */
    public function current();
}
