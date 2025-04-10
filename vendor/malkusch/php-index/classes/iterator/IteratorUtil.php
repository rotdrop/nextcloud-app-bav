<?php

namespace malkusch\index;

/**
 * Helper for ResultIterator
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class IteratorUtil
{
    
    /**
     * Returns an array with the keys
     * 
     * @return array 
     */
    public static function toKeysArray(ResultIterator $iterator)
    {
        $keys = array();
        foreach ($iterator as $result) {
            $keys[] = $result->getKey();
            
        }
        return $keys;
    }
}
