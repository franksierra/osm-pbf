# OpenStreetMap Protocol Buffer reader for PHP
[![Latest version on Packagist](https://img.shields.io/packagist/v/franksierra/osm-pbf.svg)](https://packagist.org/packages/franksierra/osm-pbf)
[![Total downloads](https://img.shields.io/packagist/dt/franksierra/osm-pbf.svg)](https://packagist.org/packages/franksierra/osm-pbf)
[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)

Reads OpenStreetMap `.osm.pbf` extracts one block at a time. Access header metadata, turn dense nodes, ways, and relations into PHP arrays as you read.

## Installation

The recommended way to install osm-pbf is through
[Composer](https://getcomposer.org/).
```sh
composer require franksierra/osm-pbf
```

## Version Guidance
| Version | PHP Version  |
|---------|--------------|
| 1.2     | >=8.1,<8.7   |
| 1.0     | >=5.6        |

## Quick Start
Install the package with Composer. The reader needs PHP 8.1 or later with the zlib extension.

1. Open the file in binary mode with `fopen($path, 'rb')` and pass the handle to `new OsmPbf\Reader($stream)`.
2. Call `readFileHeader()` once before reading data blocks. Use `getBbox()` on the returned header when it contains a bounding box. The header also exposes replication metadata when present.
3. Call `next()` until it returns `false`. After each successful call, `getElements()` returns a batch with a `type` and a `data` array. Dense nodes include coordinates, ways include ordered node references, and relations include ordered members.
4. Close the file handle when finished.

```php
$path = 'file.osm.pbf';
$stream = fopen($path, 'rb');
if ($stream === false) {
    throw new RuntimeException("Cannot open PBF file: {$path}");
}

try {
    $reader = new OsmPbf\Reader($stream);
    $reader->readFileHeader();
    while ($reader->next() !== false) {
        $elements = $reader->getElements();
        // Process the records in $elements['data'].
    }
} finally {
    fclose($stream);
}
```
To resume after a checkpoint, call `skipToBlock($processedBlocks)` immediately after `readFileHeader()`. It skips that many data blocks from the current position.

## Try the example
In a checkout of this repository, run these commands from the project root. The example needs PHP 8.1 or later with the zlib extension.

```sh
composer install --no-dev
php examples/read-sample.php
```
The [runnable example](examples/read-sample.php) reads the bundled [Ashmore and Cartier Islands extract](data/test.osm.pbf) through EOF and counts elements by type.

```text
Read test.osm.pbf
node: 1842
way: 27
relation: 15
```
Pass a path to read your own extract.

```sh
php examples/read-sample.php "path/to/region.osm.pbf"
```


## Run the tests

On PHP 8.1 or later, run `composer install` to install the development dependencies. Then run `composer test` for the full suite or `composer test:geofabrik` for the offline feature test that reads the bundled extract through EOF.

## License and sample data

The PHP package is [MIT licensed](LICENSE.md).

The sample extract was processed by [Geofabrik](https://download.geofabrik.de/australia-oceania/australia/ashmore-cartier/) and contains data © [OpenStreetMap contributors](https://www.openstreetmap.org/copyright), available under the [Open Database License](https://opendatacommons.org/licenses/odbl/1-0/).
