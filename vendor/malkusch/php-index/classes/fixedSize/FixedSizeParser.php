<?php

namespace malkusch\index;

/**
 * FixedSizeIndex parser
 *
 * The parser finds key and data in the index.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class FixedSizeParser extends Parser
{

    public function parseKeys($data, $offset, $hints = self::HINT_NONE)
    {
        $isBoundary = ($hints & self::HINT_RESULT_BOUNDARY) == self::HINT_RESULT_BOUNDARY;
        if ($offset == 0) {
            $isBoundary = true;
            
        }
        
        $pregExp = \sprintf(
            "/(%s).{%d}(.{%d})/",
            $isBoundary ? '^|\n' : '\n',
            $this->getIndex()->getIndexFieldOffset(),
            $this->getIndex()->getIndexFieldLength()
        );
        \preg_match_all(
            $pregExp,
            $data,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER
        );
        
        $keys = array();

        foreach ($matches as $match) {
            $keyOffset = $offset + $match[0][1] + 1;
            $key = trim($match[2][0]);
            
            $result = new Result();
            $result->setKey($key);
            $result->setOffset($keyOffset);
            $result->setIndex($this->getIndex());
            
            $keys[] = $result;

        }
        
        // The first match doesn't begin with \n
        if ($isBoundary && ! empty($keys)) {
            $keys[0]->setOffset($offset);
            
        }
        
        return $keys;
    }
    
    /**
     * Returns the data container which starts at $offset
     *
     * The offset is a result of parseKeys().
     *
     * @param int $offset Offset of the container
     *
     * @return string
     * @see Parser::parseKeys()
     * @throws ReadDataIndexException
     */
    public function getData($offset)
    {
        $filePointer = $this->getIndex()->getFile()->getFilePointer();
        \fseek($filePointer, $offset);
        $data = \fgets($filePointer);
        
        if ($data === false) {
            $error = \error_get_last();
            throw new ReadDataIndexException("Failed to read data: $error");
            
        }
        
        // strip the trailing \n
        if (! \feof($filePointer)) {
            $data = substr($data, 0, -1);
            
        }
        
        return $data;
    }
    
    /**
     * Returns the index
     *
     * @return FixedSizeIndex
     */
    public function getIndex()
    {
        return parent::getIndex();
    }
}
