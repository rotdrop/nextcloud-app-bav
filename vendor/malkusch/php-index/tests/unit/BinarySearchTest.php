<?php

namespace malkusch\index\test;

use malkusch\index as index;

require_once __DIR__ . "/../classes/AbstractTest.php";

/**
 * Tests the class BinarySearch
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 * @see      BinarySearch
 */
class BinarySearchTest extends AbstractTest
{

    /**
     * Tests increasing the sector count on large containers
     *
     * @return void
     */
    public function testIncreaseSectorCount()
    {
        $generator = new FixedSizeIndexGenerator();
        $oldReadLength = $generator->getIndex()->getKeyReader()->getReadLength();
        $expectedFactor = 3;
        
        $generator->setIndexLength(100);
        $generator->setMinimumDataSize($oldReadLength * $expectedFactor);
        
        

        $index = $generator->getIndex();
        $binarySearch = new index\BinarySearch($index);
        $this->assertNotEmpty($binarySearch->search(3)->getData());
        
        $this->assertGreaterThanOrEqual(
            $oldReadLength * $expectedFactor,
            $index->getKeyReader()->getReadLength()
        );
    }
    
    /**
     * Test for the search complexity
     * 
     * @dataProvider provideTestComplexity
     */
    public function testComplexity(IndexGenerator $generator)
    {
        $index = $generator->getIndex();
        foreach ($index as $result) {
            $counter = new SplitCounter();
            $index->search($result->getKey());
            $counter->stopCounting();
            $this->assertComplexity($generator, $counter);
            
        }
    }
    
    public function provideTestComplexity()
    {
        $cases = array();
        
        $generator = new FixedSizeIndexGenerator();
        $generator->setIndexLength(10000);
        $cases[] = array($generator);
        
        return $cases;
    }
}
