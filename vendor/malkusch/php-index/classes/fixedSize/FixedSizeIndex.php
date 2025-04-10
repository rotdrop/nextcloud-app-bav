<?php

namespace malkusch\index;

/**
 * Index in a plain text file.
 * 
 * The index for this data structure resides always at the same offset in each
 * line and has a fixed length.
 * 
 * 001 Payload 1
 * 002 Payload 2
 * 003 Payload 3
 * 004 Payload 4
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class FixedSizeIndex extends Index
{
    
    /**
     * @var int
     */
    private $indexFieldOffset = 0;
    
    /**
     * @var int
     */
    private $indexFieldLength = 0;
    
    /**
     * @var int
     */
    private $lineLength;
    
    /**
     * Sets the index file, index offset and the index length
     *
     * @param string $path             Index file
     * @param string $indexFieldOffset Index field offset
     * @param string $indexFieldLength Index field length
     *
     * @throws FileExistsIOException
     * @throws IOIndexException
     */
    public function __construct($path, $indexFieldOffset, $indexFieldLength)
    {
        parent::__construct($path);
        
        $this->indexFieldOffset = $indexFieldOffset;
        $this->indexFieldLength = $indexFieldLength;
        
        $dummyLine = fgets($this->getFile()->getFilePointer());
        if (! $dummyLine) {
            throw new IOIndexException("Could not read line length");

        }
        $this->lineLength = strlen($dummyLine);
    }
    
    /**
     * Returns the length of a line
     * 
     * A line includes the line break.
     * 
     * @return int
     */
    public function getLineLength()
    {
        return $this->lineLength;
    }
    
    /**
     * Returns a parser for this index
     *
     * @return FixedSizeParser
     */
    public function getParser()
    {
        return new FixedSizeParser($this);
    }
    
    /**
     * Returns the offset of the index field
     * 
     * @return int
     */
    public function getIndexFieldOffset()
    {
        return $this->indexFieldOffset;
    }
    
    /**
     * Returns the length of the index field
     * 
     * @return int
     */
    public function getIndexFieldLength()
    {
        return $this->indexFieldLength;
    }
}
