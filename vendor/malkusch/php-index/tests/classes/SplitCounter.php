<?php

namespace malkusch\index\test;

use malkusch\index as index;

/**
 * Counts the splits in a binary search
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class SplitCounter implements \Countable
{

    /**
     * Splitting context
     */
    const CONTEXT_SPLITTING = "splitting";
    /**
     * Searching context
     */
    const CONTEXT_SEARCHING = "searching";

    /**
     * @var string
     */
    private $context = self::CONTEXT_SEARCHING;
    /**
     * @var int
     */
    private $count = 0;

    /**
     * Starts the counting for the splits
     */
    public function __construct()
    {
        \register_tick_function(array($this, "countSplit"));
        declare(ticks=1);
    }

    /**
     * Tick handler for counting splits
     *
     * @return void
     */
    public function countSplit()
    {
        $backtrace = \debug_backtrace(false);
        if (\strpos($backtrace[1]["function"], "split") !== false) {
            if ($this->context == self::CONTEXT_SEARCHING) {
                $this->context = self::CONTEXT_SPLITTING;
                $this->count++;

            }
        } elseif (\strpos($backtrace[1]["function"], "search") !== false) {
            $this->context = self::CONTEXT_SEARCHING;

        }
    }

    /**
     * Returns the counted splits
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Stops counting
     *
     * @return void
     */
    public function stopCounting()
    {
        \unregister_tick_function(array($this, "countSplit"));
    }
}
