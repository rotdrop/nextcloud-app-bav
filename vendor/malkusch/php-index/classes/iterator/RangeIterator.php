<?php

namespace malkusch\index;

/**
 * Range iterator
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class RangeIterator extends \IteratorIterator implements ResultIterator
{
    
    /**
     * @var Range
     */
    private $range;
    
    public function __construct(IndexIterator $iterator, Range $range)
    {
        parent::__construct($iterator);
        $this->range    = $range;
    }
    
    public function valid()
    {
        if (! parent::valid()) {
            return false;
            
        }
        return $this->range->contains($this->current()->getKey());
    }
}
