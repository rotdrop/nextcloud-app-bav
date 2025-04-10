<?php

namespace malkusch\index\test;

use malkusch\index as index;

require_once __DIR__ . "/../classes/AbstractTest.php";

/**
 * Test for Range
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class RangeTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Tests that the borders are contained by the range
     * 
     * @dataProvider provideTestContains
     */
    public function testInclusiveContains($min, $max)
    {
        $range = new index\Range($min, $max);
        $range->setInclusive(true);

        // Min
        $this->assertTrue($range->contains($min));
        $this->assertFalse($range->contains($min - 0.1));
        
        // Max
        $this->assertTrue($range->contains($max));
        $this->assertFalse($range->contains($max + 0.1));
    }
    
    /**
     * Tests that the borders are not contained by the range
     * 
     * @dataProvider provideTestContains
     */
    public function testExclusiveContains($min, $max)
    {
        $range = new index\Range($min, $max);
        $range->setInclusive(false);

        $this->assertFalse($range->contains($min));
        $this->assertFalse($range->contains($max));
    }
    
    /**
     * Tests some values in the range
     * 
     * @dataProvider provideTestContains
     */
    public function testContains($min, $max)
    {
        $range = new index\Range($min, $max);

        $step = 0.1;
        for ($i = $min + $step; $i < $max; $i += $step) {
            $this->assertTrue($range->contains($i));
            
        }
    }
    
    /**
     * Tests Range::setInclusive()
     */
    public function testSetInclusive()
    {
        $range = new index\Range(0, 0);
        $range->setInclusive(true);
        $this->assertTrue($range->isInclusive());
        
        $range = new index\Range(0, 0);
        $range->setInclusive(false);
        $this->assertFalse($range->isInclusive());
    }
    
    /**
     * Test cases
     * 
     * @return array 
     */
    public function provideTestContains()
    {
        return array(
            array(0, 0),
            array(0, 1),
            array(-1, 1),
        );
    }
}
