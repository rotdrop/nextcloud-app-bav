#! /usr/bin/php
<?php

/**
 * XML index example
 *
 * The example shows how to use an XML document with sorted elements.
 *
 * @author    Markus Malkusch <markus@malkusch.de>
 * @link      https://github.com/malkusch/php-index
 */

use malkusch\index\XMLIndex;
use malkusch\index\IOIndexException;
use malkusch\index\ReadDataIndexException;

// Include the autoloader
require_once __DIR__ . "/../../../autoloader/autoloader.php";

try {
    // Define the index
    $index = new XMLIndex(
        __DIR__ . "/index.xml", // Index file
        "container", // Container element
        "index" // Index attribute of the container element
    );

    // Search the data for the key 1234
    $data = $index->search(1234);
    
    if ($data != null) {
        /*
         * The returned data is the XML as string. You can use SimpleXML to browse
         * the data.
         *
         * @see SimpleXML
         */
        $xml = new \SimpleXMLElement($data);
        \var_dump((string) $xml->payload);
        
    }

    // Search the data for the nonexistend key 12345
    $data = $index->search(12345);
    if ($data == null) {
        echo "Didn't find key 12345\n";
        
    }

} catch (IOIndexException $e) {
    // IO Error during opening or reading the index
    echo $e->getMessage(), "\n";

} catch (ReadDataIndexException $e) {
    // Error while reading found data
    echo $e->getMessage(), "\n";

}