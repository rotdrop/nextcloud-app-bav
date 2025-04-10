<?php

namespace malkusch\index;

/**
 * Result
 *
 * A search returns a Result object with the data, key and the offset
 * in the index file.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class Result
{
    
    /**
     * @var String
     */
    private $key = "";
    
    /**
     * @var String
     */
    private $data;
    
    /**
     * @var int
     */
    private $offset = 0;
    
    /**
     * @var Index 
     */
    private $index;
    
    /**
     * Sets the index
     * 
     * @param Index $index 
     * 
     * @return void
     */
    public function setIndex(Index $index)
    {
        $this->index = $index;
    }
    
    /**
     * Sets the key
     *
     * @param String $key 
     * 
     * @return void
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
    
    /**
     * Returns the key
     *
     * @return String
     */
    public function getKey()
    {
        return $this->key;
    }
    
    /**
     * Returns the data
     *
     * @return String
     */
    public function getData()
    {
        if (is_null($this->data)) {
            $this->data = $this->index->getParser()->getData($this->getOffset());
            
        }
        return $this->data;
    }
    
    /**
     * Sets the offset
     *
     * @param int $offset 
     * 
     * @return void
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
    
    /**
     * Returns the offset
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
}
