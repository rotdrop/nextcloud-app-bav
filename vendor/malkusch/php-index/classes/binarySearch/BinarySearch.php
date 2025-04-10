<?php

namespace malkusch\index;

/**
 * Binary search
 *
 * This class searches in a sorted index.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class BinarySearch
{
    
    const DIRECTION_FORWARD  = 1;
    const DIRECTION_BACKWARD = -1;

    /**
     * @var Index
     */
    private $index;
    /**
     * @var ByteRange
     */
    private $range;

    /**
     * Sets the index
     *
     * @param Index $index Index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->range = new ByteRange(0, $index->getFile()->getFileSize());
    }
    
    /**
     * Searches for a key or some neighbour
     *
     * If it doesn't find the key. A neighbour will be returned. The
     * neighbour mustn't be the closest neighbour. It's just a good hint
     * where the key should be expected.
     * 
     * Returns null if no key could be found at all.
     * 
     * @param string $key Key
     *
     * @return Result
     * @throws IOIndexException
     */
    public function search($key)
    {
        // split the range
        $splitOffset = $this->getSplitOffset();
        
        // search right side
        $keys = $this->index->getKeyReader()->readKeys($splitOffset, self::DIRECTION_FORWARD);
        $foundKey = $this->findKey($key, $keys);
        // found
        if (! is_null($foundKey)) {
            return $foundKey;
            
        }
        // check if search should terminate
        if ($this->isKeyRange($key, $keys)) {
            return \reset($keys);
            
        }
        
        // If found keys are smaller continue in the right side
        if (! empty($keys) && \end($keys)->getKey() < $key) {
            $newOffset = $splitOffset + $this->index->getKeyReader()->getReadLength();
            // Stop if beyond index
            if ($newOffset >= $this->index->getFile()->getFileSize()) {
                return \end($keys);
                
            }
            $newLength =
                $this->range->getLength() - ($newOffset - $this->range->getOffset());
            $this->range->setOffset($newOffset);
            $this->range->setLength($newLength);
            return $this->search($key);
            
        }
        
        // Look at the key, which lies in both sides
        $centerKeyOffset = empty($keys)
            ? $this->range->getNextByteOffset()
            : \reset($keys)->getOffset();
        $keys = $this->index->getKeyReader()->readKeys($centerKeyOffset, self::DIRECTION_BACKWARD);
        $foundKey = $this->findKey($key, $keys);
        // found
        if (! is_null($foundKey)) {
            return $foundKey;
            
        }
        // terminate if no more keys in the index
        if (empty($keys)) {
            return null;
            
        }
        // check if search should terminate
        if ($this->isKeyRange($key, $keys)) {
            return \reset($keys);
            
        }
        
        // Finally continue searching in the left side
        $newLength = \reset($keys)->getOffset() - $this->range->getOffset() - 1;
        if ($newLength >= $this->range->getLength()) {
            return \reset($keys);
            
        }
        $this->range->setLength($newLength);
        return $this->search($key);
    }
    
    /**
     * Returns true if the key is expected to be in the key list
     * 
     * If the key list is a subset of the index, and the key sould not be in 
     * this list, the key is nowhere else in the index.
     *
     * @param type $key
     * @param array $keys
     * 
     * @return bool
     */
    private function isKeyRange($key, Array $keys)
    {
        if (empty($keys)) {
            return false;
            
        }
        return \reset($keys)->getKey() <= $key
            && \end($keys)->getKey() >= $key;
    }
    
    /**
     * @param String $key
     * @param array $foundKeys
     * @return Result
     */
    private function findKey($key, Array $foundKeys)
    {
        foreach ($foundKeys as $foundKey) {
            if ($foundKey->getKey() == $key) {
                return $foundKey;
                
            }
        }
        return null;
    }
    
    /**
     * Returns the offset for the split
     * 
     * @return int 
     */
    private function getSplitOffset()
    {
        $blocks = (int) $this->range->getLength() / $this->index->getKeyReader()->getReadLength();
        $centerBlock = (int) $blocks / 2;
        return $this->range->getOffset() + $centerBlock * $this->index->getKeyReader()->getReadLength();
    }
}
