<?php

namespace malkusch\index;

/**
 * Search range
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class Range
{
    
    /**
     * @var String
     */
    private $min = "";
    
    /**
     * @var String
     */
    private $max = "";
    
    /**
     * @var bool
     */
    private $inclusive = true;
    
    /**
     * Sets the borders of the range
     *
     * @param int $min
     * @param int $max 
     */
    public function __construct($min, $max)
    {
        if ($min > $max) {
            $this->min = $max;
            $this->max = $min;
            
        } else {
            $this->min = $min;
            $this->max = $max;
            
        }
    }
    
    /**
     * Returns an empty range
     * 
     * An empty range doesn't contain any key
     * 
     * @return Range
     */
    public static function getEmptyRange()
    {
        $range = new self(0, 0);
        $range->setInclusive(false);
        return $range;
    }
    
    /**
     * Returns true if key is inside this range
     * 
     * @param String $key 
     * 
     * @return bool
     */
    public function contains($key)
    {
        if ($this->inclusive && in_array($key, array($this->min, $this->max))) {
            return true;
            
        }
        return $key > $this->min && $key < $this->max;
    }
    
    /**
     * Sets if the range is including or excluding the borders
     *
     * @param bool $inclusive 
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = $inclusive;
    }
    
    /**
     * Returns if the range is including or excluding the borders
     *
     * @return bool
     */
    public function isInclusive()
    {
        return $this->inclusive;
    }
    
    /**
     * Returns the greater border
     *
     * @return String
     */
    public function getMax()
    {
        return $this->max;
    }
    
    /**
     * Returns the lesser border
     *
     * @return String
     */
    public function getMin()
    {
        return $this->min;
    }
}
