<?php

namespace malkusch\index\test;

use malkusch\index as index;

require_once __DIR__ . "/../classes/AbstractTest.php";

/**
 * Tests the xml index
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class FixedSizeIndexTest extends AbstractTest
{
    
    /**
     * Tests getLineLength()
     * 
     * @dataProvider provideTestGetLineLength
     * @see FixedSizeIndex::getLineLength()
     */
    public function testGetLineLength($expectedLineLength, $file)
    {
        $index = new index\FixedSizeIndex($file, 0, 1);
        $this->assertEquals($expectedLineLength, $index->getLineLength());
    }
    
    /**
     * Test cases for testGetLineLength()
     * 
     * @return array
     * @see testGetLineLength()
     */
    public function provideTestGetLineLength()
    {
        return array(
            array(5, __DIR__ . "/../index/fixedSize/one-line.txt"),
            array(6, __DIR__ . "/../index/fixedSize/simple.txt")
        );
    }
    
    /**
     * Tests finding all keys with different index configurations 
     * 
     * @dataProvider provideTestIndexConfiguration
     */
    public function testIndexConfiguration($offset, $indexFieldLength, $length)
    {
        $generator = new FixedSizeIndexGenerator();
        $generator->setIndexFieldOffset($offset);
        $generator->setIndexFieldLength($indexFieldLength);
        $generator->setIndexLength($length);
        
        $index = $generator->getIndex();
        for ($key = 0; $key < $length; $key++) {
            $result  = $index->search($key);
            $this->assertNotNull($result, "key: $key");
            $this->assertRegExp(
                '/data_' . $key . '_.*\$/s',
                $result->getData(),
                "key: $key"
            );
            
        }
    }
    
    public function provideTestIndexConfiguration()
    {
        $cases = array();
        $lengths = array(1, 10, 100, index\File::DEFAULT_BLOCK_SIZE * 12);
        $offsets = array(0, 1, index\File::DEFAULT_BLOCK_SIZE * 12);
        foreach ($lengths as $length) {
            foreach ($offsets as $offset) {
                $indexFieldLength = 1;
                $cases = \array_merge($cases, array(
                    array($offset, $indexFieldLength, $length)
                ));
                
            }
        }
        return $cases;
    }
}
