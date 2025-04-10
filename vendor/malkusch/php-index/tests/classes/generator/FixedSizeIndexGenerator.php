<?php

namespace malkusch\index\test;

use malkusch\index as index;

/**
 * Generates a plain text index
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class FixedSizeIndexGenerator extends IndexGenerator
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
     * Sets the index offset
     *
     * @param int $indexFieldOffset offset of the index in each line
     * 
     * @return void
     */
    public function setIndexFieldOffset($indexFieldOffset)
    {
        $this->indexFieldOffset = $indexFieldOffset;
    }
    
    /**
     * Sets the index length
     *
     * @param int $indexFieldLength length of the index
     * 
     * @return void
     */
    public function setIndexFieldLength($indexFieldLength)
    {
        $this->indexFieldLength = $indexFieldLength;
    }
    
    /**
     * Returns the length of the index field
     * 
     * @return int
     */
    public function getIndexFieldLength()
    {
        return $this->indexFieldLength < strlen($this->getMaximum())
            ? strlen($this->getMaximum())
            : $this->indexFieldLength;
    }
    
    /**
     * @return string
     */
    public function generateData($key)
    {
        $padding = str_repeat(" ", $this->indexFieldOffset);
        $indexKey = str_pad($key, $this->getIndexFieldLength());
        $data = parent::generateData($key);
        return $padding . $indexKey . $data;
    }
    
    /**
     * Creates a new Index file
     *
     * @var string $file Path to the index
     *
     * @return void
     * @throws CreateFileIndexTestException
     */
    protected function createIndexFile($file)
    {
        $filepointer = @fopen($file, "w");
        if (!is_resource($filepointer)) {
            throw new CreateFileIndexTestException(
                sprintf(
                    "Could not open '%s': %s",
                    $file,
                    error_get_last()
                )
            );
            
        }
        
        for ($key = $this->getMinimum(); $key <= $this->getMaximum(); $key += $this->getStepSize()) {
            $line  = $this->generateData($key) . "\n";
            $bytes = @fputs($filepointer, $line);
            if ($bytes != strlen($line)) {
                throw new CreateFileIndexTestException(
                    sprintf(
                        "Could not write line '%s': %s",
                        $line,
                        error_get_last()
                    )
                );
                
            }
        }
        fclose($filepointer);
    }
    
    /**
     * Creates a new Index
     *
     * @var string $file Path to the index
     *
     * @return FixedSizeIndex
     */
    protected function createIndex($file)
    {
        return new index\FixedSizeIndex(
            $file,
            $this->indexFieldOffset,
            $this->getIndexFieldLength()
        );
    }
    
    /**
     * Returns the characteristic properties of this index
     * 
     * This is used for the file name creation.
     *
     * @return array
     */
    protected function getIndexProperties()
    {
        return array_merge(
            parent::getIndexProperties(),
            array(
                $this->indexFieldOffset,
                $this->getIndexFieldLength(),
            )
        );
    }
}
