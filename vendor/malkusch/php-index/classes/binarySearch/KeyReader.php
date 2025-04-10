<?php

namespace malkusch\index;

/**
 * Helper for doing file operations in a file
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class KeyReader
{
    
    const DIRECTION_FORWARD  = 1;
    const DIRECTION_BACKWARD = -1;
    
    /**
     * @var int
     */
    private $readBlockCount = 1;
    /**
     * @var Index
     */
    private $index;
    
    /**
     * Sets the index
     */
    public function setIndex(Index $index)
    {
        $this->index = $index;
    }
    
    /**
     * Returns keys from a offset
     * 
     * The read range will be increased until at least one key will be found
     * or the end of file was reached.
     *
     * @param int $offset
     * @param int $direction
     * @return array
     * @throws IOIndexException
     */
    public function readKeys($offset, $direction, $hints = Parser::HINT_NONE)
    {
        // If reading backwards, shift the offset left
        $shiftedOffset = $direction == self::DIRECTION_BACKWARD
                ? $offset - $this->getReadLength()
                : $offset;
        
        //TODO shift to a blocksize chunk
        
        // Don't shift too far
        if ($shiftedOffset < 0) {
            $shiftedOffset = 0;

        }
        
        // Read data
        \fseek($this->index->getFile()->getFilePointer(), $shiftedOffset);
        $data = \fread(
            $this->index->getFile()->getFilePointer(),
            $this->getReadLength()
        );
        if ($data === false) {
            if (\feof($this->index->getFile()->getFilePointer())) {
                return array();

            } else {
                throw new IOIndexException("Could not read file");

            }
        }
        
        // Parse the read data
        $keys = $this->index->getParser()->parseKeys($data, $shiftedOffset, $hints);
        
        // Read more data if no keys were found
        if (empty($keys)) {
            // Only increase if there exists more data
            if ($direction == self::DIRECTION_BACKWARD && $shiftedOffset == 0) {
                return array();
                
            } elseif ($direction == self::DIRECTION_FORWARD
                        && $shiftedOffset + $this->getReadLength()
                           >= $this->index->getFile()->getFileSize()
            ) {
                return array();
                
            }
            
            $this->increaseReadLength();
            return $this->readKeys($offset, $direction);
            
        }
        
        return $keys;
    }
    
    /**
     * Returns the length of one read operation 
     * 
     * @return int
     */
    public function getReadLength()
    {
        return $this->index->getFile()->getBlockSize() * $this->readBlockCount;
    }
    
    /**
     * Increases the read size
     */
    private function increaseReadLength()
    {
        $this->readBlockCount = $this->readBlockCount * 2;
    }
}
