<?php

namespace malkusch\index\test;

use malkusch\index as index;

/**
 * Generates a XML index
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class XMLIndexGenerator extends IndexGenerator
{

    /**
     * Payload element
     */
    const ELEMENT_PAYLOAD = "payload";

    /**
     * @var bool
     */
    private $formatOutput = true;
    /**
     * @var string
     */
    private $element = "";
    /**
     * @var string
     */
    private $attribute = "";

    /**
     * Sets the element's name and the index attribute
     *
     * @param string $element   Element's name
     * @param string $attribute Index attribute
     */
    public function __construct($element = "container", $attribute = "index")
    {
        $this->element   = $element;
        $this->attribute = $attribute;
    }
    
    /**
     * Sets if the output will be nice XML
     *
     * Per default it is nice XML.
     *
     * @param bool $isFormatted Is output formatted
     * 
     * @return void
     */
    public function formatOutput($isFormatted)
    {
        $this->formatOutput = $isFormatted;
    }

    /**
     * Returns the container name
     *
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Returns the index attribute
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Creates a new Index
     *
     * @var string $file Path to the index
     *
     * @return XmlIndex
     */
    protected function createIndex($file)
    {
        return new index\XmlIndex($file, $this->element, $this->attribute);
    }

    /**
     * Returns the index file name without the directory path
     *
     * @return string
     */
    protected function getIndexFileName()
    {
        return "$this->element.$this->attribute-{$this->getIndexLength()}"
            . "-{$this->getStepSize()}-$this->formatOutput.xml";
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
        $document = new \DOMDocument();
        $document->formatOutput = $this->formatOutput;

        $root = $document->createElement("document");
        $document->appendChild($root);

        for ($key = 0; $key < $this->getIndexLength(); $key += $this->getStepSize()) {
            $container = $document->createElement($this->element);
            $root->appendChild($container);

            // Append the index
            $attribute = $document->createAttribute($this->attribute);
            $container->appendChild($attribute);
            $attribute->value = $key;

            // Append some payload
            $payload = $document->createElement(self::ELEMENT_PAYLOAD);
            $container->appendChild($payload);
            $payload->appendChild(
                $document->createCDATASection($this->generateData($key))
            );

        }

        $bytes = $document->save($file);
        if ($bytes === false) {
            throw new CreateFileIndexTestException(
                "Could not create test file"
            );

        }
    }
}
