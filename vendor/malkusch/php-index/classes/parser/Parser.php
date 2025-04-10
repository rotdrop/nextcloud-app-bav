<?php

namespace malkusch\index;

/**
 * Index parser
 *
 * The parser finds key and data in the index.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
abstract class Parser
{

    const HINT_NONE = 0;
    const HINT_RESULT_BOUNDARY = 1;
    
    /**
     * @var Index
     */
    private $index;

    /**
     * Returns Results with keys.
     *
     * $data is parsed for keys. The found keys are returned.
     *
     * @param string $data   Parseable data
     * @param int    $offset The position where the date came from
     * @param int    $hints  Parse hints
     *
     * @return Result[]
     */
    abstract public function parseKeys($data, $offset, $hints = self::HINT_NONE);
    
    /**
     * Returns the data container which starts at $offset
     *
     * The offset is a result of pareKeys().
     *
     * @param int $offset Offset of the container
     *
     * @return string
     * @see Parser::parseKeys()
     * @throws ReadDataIndexException
     */
    abstract public function getData($offset);

    /**
     * Sets the index
     *
     * @param Index $index Index
     */
    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    /**
     * Returns the index
     *
     * @return Index
     */
    public function getIndex()
    {
        return $this->index;
    }
}
