<?php

namespace malkusch\index;

/**
 * Index in XML
 *
 * The index is an attribute of a sorted container element.
 *
 * <code>
 * <index>
 *   <container key="a">
 *      Payload A
 *   </container>
 *   <container key="b">
 *      Payload B
 *   </container>
 *   <container key="c">
 *      Payload C
 *   </container>
 * </index>
 * </code>
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class XmlIndex extends Index
{
    
    /**
     * @var string
     */
    private $element = "";
    /**
     * @var string
     */
    private $attribute = "";

    /**
     * Sets the index file, container name and the index attribute
     *
     * @param string $path      Index file
     * @param string $element   Container name
     * @param string $attribute Index attribute name
     *
     * @throws FileExistsIOException
     * @throws IOIndexException
     */
    public function __construct($path, $element, $attribute)
    {
        parent::__construct($path);
        
        $this->element   = $element;
        $this->attribute = $attribute;
    }

    /**
     * Returns the container element name
     *
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Returns the index attribute name
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Returns a parser for this index
     *
     * @return XmlParser
     */
    public function getParser()
    {
        return new XmlParser($this);
    }
}
