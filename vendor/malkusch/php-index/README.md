# About PHP-Index

This library provides an API to perform binary search operations on a sorted
index. The index can be a XML document, an CSV document, or an arbitrary text
file where the key has a fixed position. You can easily implement your own
index. This API comes handy on any sorted data structure where realtime search
operations are necessary without the detour of a DBS import.


# Installation

Use [Composer](https://getcomposer.org/):

```json
{
    "require": {
        "malkusch/php-index": "~0.1"
    }
}
```


# Usage

Have a look at the docs/examples/ folder. You can find there examples for each
implemented index structure.


# License and author

This project is free and under WTFPL.
Responsable for this project is Markus Malkusch <markus@malkusch.de>.

## Donations

If you like BAV and feel generous donate a few Bitcoins here:
[1335STSwu9hST4vcMRppEPgENMHD2r1REK](bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK)


[![Build Status](https://travis-ci.org/malkusch/php-index.svg)](https://travis-ci.org/malkusch/php-index)
