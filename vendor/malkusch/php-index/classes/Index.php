<?php

namespace malkusch\index;

/**
 * Index
 *
 * The index does a binary search on a key. That means that the data needs to
 * have a sorted index. You simply call the method Index::search() to find the
 * container for the key.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
abstract class Index implements \IteratorAggregate
{

    /**
     * @var File
     */
    private $file;
    /**
     * @var KeyReader
     */
    private $keyReader;
    

    /**
     * Returns a parser for this index
     *
     * @return Parser
     */
    abstract public function getParser();

    /**
     * Sets the index file and inits the index
     *
     * @param string $path Index file
     *
     * @throws FileExistsIOException
     * @throws IOIndexException
     */
    public function __construct($path)
    {
        $this->file = new File($path);
        
        $this->keyReader = new KeyReader();
        $this->keyReader->setIndex($this);
    }
    
    /**
     * Index iterator
     * 
     * You can iterate through the index with this iterator.
     * 
     * @return IndexIterator 
     */
    public function getIterator()
    {
        return new IndexIterator($this);
    }

    /**
     * Searches for the container with that key
     *
     * Returns null if the key wasn't found.
     * 
     * @param string $key Key in the index
     *
     * @return Result
     * @throws ReadDataIndexException
     */
    public function search($key)
    {
        $binarySearch = new BinarySearch($this);
        $result = $binarySearch->search($key);
        if (\is_null($result) || $result->getKey() != $key) {
            return null;
            
        }
        return $result;
    }
    
    /**
     * Searches a range
     * 
     * @param Range $range 
     * 
     * @return RangeIterator
     */
    public function searchRange(Range $range)
    {
        $iterator = $this->getIterator();
        
        // find start
        $start = null;
        $binarySearch = new BinarySearch($this);
        $startHint = $binarySearch->search($range->getMin());
        if ($startHint == null) {
            return new RangeIterator($iterator, Range::getEmptyRange());
            
        }
        $iterator->setOffset($startHint->getOffset(), Parser::HINT_RESULT_BOUNDARY);

        if (! $range->contains($startHint->getKey()) && $startHint->getKey() <= $range->getMin()) {
            // shift $startHint higher
            
            foreach ($iterator as $result) {
                if ($range->contains($result->getKey())) {
                    $start = $result;
                    break;
                    
                }
            }
            
        } else {
            // shift $startHint lower
            
            if ($range->contains($startHint->getKey())) {
                $start = $startHint;
                
            }
            $iterator->setDirection(KeyReader::DIRECTION_BACKWARD);
            foreach ($iterator as $result) {
                // Skip everything which is too big
                if (! $range->contains($result->getKey() && $result->getKey() >= $range->getMax())) {
                    continue;
                    
                }
                
                // shift the start left until no more key is included
                if ($range->contains($result->getKey())) {
                    $start = $result;
                    
                } else {
                    break;
                    
                }
            }
            
        }
        if (is_null($start)) {
            return new RangeIterator($iterator, Range::getEmptyRange());

        }
        
        $iterator = $this->getIterator();
        $iterator->setOffset($start->getOffset(), Parser::HINT_RESULT_BOUNDARY);
        return new RangeIterator($iterator, $range);
    }

    /**
     * Returns the index file
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Returns the KeyReader
     *
     * @return KeyReader
     */
    public function getKeyReader()
    {
        return $this->keyReader;
    }
}
