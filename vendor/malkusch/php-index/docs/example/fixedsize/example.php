#! /usr/bin/php
<?php

/**
 * Index in a plain text file example
 *
 * The example shows how to use a plain text file with a defined index in each
 * line.
 * 
 * @author    Markus Malkusch <markus@malkusch.de>
 * @link      https://github.com/malkusch/php-index
 */

use malkusch\index\FixedSizeIndex;
use malkusch\index\IOIndexException;
use malkusch\index\ReadDataIndexException;

// Include the autoloader
require_once __DIR__ . "/../../../autoloader/autoloader.php";

try {
    // Define the index
    $index = new FixedSizeIndex(
        __DIR__ . "/index.txt", // Index file
        0, // offset of the index in each line
        8 // length of the index
    );

    // Search the data for the key 10077777
    $data = $index->search(10077777);

    if ($data != null) {
        echo $data->getData(), "\n";
        
    } else {
        echo "Didn't find key 10020500\n";
        
    }

    // Search the data for the nonexistend key 12345.
    $data = $index->search(12345);
    
    if ($data != null) {
        echo $data->getData(), "\n";
        
    } else {
        echo "Didn't find key 12345\n";
        
    }

} catch (IOIndexException $e) {
    // IO Error during opening or reading the index
    echo $e->getMessage(), "\n";

} catch (ReadDataIndexException $e) {
    // Error while reading found data
    echo $e->getMessage(), "\n";

}