<?php

namespace malkusch\index\test;

use malkusch\index as index;

/**
 * Setup autoloader
 */
require_once __DIR__ . "/../autoloader/autoloader.php";
require_once __DIR__ . "/../../autoloader/autoloader.php";


/**
 * Abstract test
 *
 * The purpose of this class is defining an autoloader.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Asserts the complexity for an index
     *
     * The complexity is always O(log(n)).
     *
     * @param IndexGenerator $generator Index generator
     * @param SplitCounter   $counter   Split counter in binary search
     *
     * @return float
     */
    protected function assertComplexity(
        IndexGenerator $generator,
        SplitCounter $counter
    ) {
        if (\count($counter) == 0) {
            return;
            
        }
        $this->assertLessThan(
            \log($generator->getIndexLength(), 2) * 2,
            \count($counter)
        );
    }
}
