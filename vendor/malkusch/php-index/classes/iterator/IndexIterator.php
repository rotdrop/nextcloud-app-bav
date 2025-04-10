<?php

namespace malkusch\index;

/**
 * Index Iterator
 * 
 * This iterator returns Result objects.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class IndexIterator implements ResultIterator
{
    
    /**
     * @var int
     */
    private $offset;
    
    /**
     * @var int
     */
    private $parseHints = Parser::HINT_NONE;
    
    /**
     * @var int 
     */
    private $direction = KeyReader::DIRECTION_FORWARD;
    
    /**
     * @var Index 
     */
    private $index;
    
    /**
     * @var ArrayIterator 
     */
    private $iterator;
    
    /**
     * Sets the index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
        $this->iterator = new \ArrayIterator();
    }

    /**
     * Sets the iteration direction
     *
     * @param int $direction Direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
    
    /**
     * Sets a offset
     *
     * @param int $offset     Offset
     * @param int $parseHints Parse hints
     */
    public function setOffset($offset, $parseHints = Parser::HINT_NONE)
    {
        $this->offset = $offset;
        $this->parseHints = $parseHints;
    }

    /**
     * @return Result
     */
    public function current()
    {
        return $this->iterator->current();
    }

    public function key()
    {
        return $this->current()->getKey();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function rewind()
    {
        $this->iterator = new \ArrayIterator();
        if (is_null($this->offset)) {
            if ($this->direction == KeyReader::DIRECTION_BACKWARD) {
                $this->setOffset($this->index->getFile()->getFileSize() - 1);
                
            } else {
                $this->setOffset(0);
                
            }
        }
    }

    public function valid()
    {
        if (! $this->iterator->valid()) {
            // terminate at the left end
            if ($this->direction == KeyReader::DIRECTION_BACKWARD && $this->offset < 0) {
                return false;

            }
            $this->iterator = new \ArrayIterator($this->iterateKeyReader());
            
        }
        return $this->iterator->valid();
    }
    
    /**
     * Iterates in the index and shifts the offset
     * 
     * @return Array
     */
    private function iterateKeyReader()
    {
        $results = $this->index->getKeyReader()->readKeys(
            $this->offset,
            $this->direction,
            $this->parseHints
        );
        if (empty($results)) {
            return $results;
            
        }
        
        // reverse order if iterating backwards
        if ($this->direction == KeyReader::DIRECTION_BACKWARD) {
            // $results = array_reverse($results);
            
            // trim results to the offset and reverse the order
            $revertBackwardResults = array();
            foreach ($results as $result) {
                if ($result->getOffset() > $this->offset) {
                    break;
                    
                }
                array_unshift($revertBackwardResults, $result);
                
            }
            $results = $revertBackwardResults;

        }

        // shift offset
        $end = end($results);
        switch ($this->direction) {

            case KeyReader::DIRECTION_FORWARD:
                $this->setOffset($end->getOffset() + strlen($end->getData()));
                break;

            case KeyReader::DIRECTION_BACKWARD:
                $this->setOffset($end->getOffset() - 1);
                break;

            default:
                throw new \LogicException("invalid direction");

        }
        
        // remove duplicates (BACKWARD only)
        $this->avoidBackwardDuplicates($results);
        
        reset($results);
        return $results;
    }
    
    /**
     * Removes results which were found in the previous iteration.
     * 
     * This applies to backward iteration only
     */
    private function avoidBackwardDuplicates(array &$results)
    {
        // applies only on the last step of backward iteration
        if ($this->direction == KeyReader::DIRECTION_FORWARD || $this->offset >= 0) {
            return;
            
        }
        
        // get the minimum of the last iteration
        $lastResults = $this->iterator->getArrayCopy();
        if (empty($lastResults)) {
            return;
            
        }
        $lastMinimum = end($lastResults)->getKey();
        
        // reduce the results
        $reducedResults = array();
        foreach ($results as $result) {
            if ($result->getKey() >= $lastMinimum) {
                continue;

            }
            $reducedResults[] = $result;

        }
        $results = $reducedResults;
    }
}
