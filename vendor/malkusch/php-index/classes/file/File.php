<?php

namespace malkusch\index;

/**
 * Wrapper for file operations and informations
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class File
{
    
    /**
     * Sector size
     */
    const DEFAULT_BLOCK_SIZE = 512;
    
    /**
     * @var int
     */
    private $blocksize = self::DEFAULT_BLOCK_SIZE;
    /**
     * @var String
     */
    private $path = "";
    /**
     * @var int
     */
    private $size = 0;
    /**
     * @var resource
     */
    private $filePointer;
    
    /**
     * Sets the index file and opens a file pointer
     *
     * @param string $path Index file
     *
     * @throws FileExistsIOException
     * @throws IOIndexException
     */
    public function __construct($path)
    {
        $this->path = $path;
        
        // Open the file
        $this->filePointer = @\fopen($path, "rb");
        if (! \is_resource($this->filePointer)) {
            if (! \file_exists($path)) {
                throw new FileExistsIOException("'$path' doesn't exist.");

            }
            $errors = \error_get_last();
            throw new IOIndexException($errors["message"]);

        }
        
        // Read the filesystem's blocksize
        $stat = \stat($path);
        if (\is_array($stat) && isset($stat["blksize"]) && $stat["blksize"] > 0) {
            $this->blocksize = $stat["blksize"];
            
        }
        
        // Read the size
        $this->size = \filesize($path);
        if ($this->size === false) {
            throw new IOIndexException("Can't read size of '$path'");
            
        }
    }
    
    /**
     * Returns an open file pointer for reading in binary mode
     *
     * @return resource
     */
    public function getFilePointer()
    {
        return $this->filePointer;
    }
    
    /**
     * Returns the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Returns the file size
     * 
     * @return int 
     */
    public function getFileSize()
    {
        return $this->size;
    }
    
    /**
     * Returns the blocksize of the file's filesystem
     * 
     * @return int 
     */
    public function getBlockSize()
    {
        return $this->blocksize;
    }
}
