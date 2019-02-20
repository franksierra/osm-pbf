<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/19/2019
 * Time: 8:22 PM
 */
// Script end
function rutime($ru, $rus, $index)
{
    return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
        - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
}

$rustart = getrusage();

require_once __DIR__ . '/vendor/autoload.php';

use OSMReader\OSMReader;

$handle = fopen("data/ecuador-latest-internal.osm.pbf", "rb");

$osm = new OSMReader($handle);

$file_header = $osm->readHeader();

$index = 1;
while ($data = $osm->next()) {
    /**
     * If you need a string get it using this
     */
    // $data->getStringtable()->getS()[5];

//    $data->getPrimitivegroup()->getIterator();
    $reader = $osm->getReader();
    $current = $reader->getPosition();
    $total = $reader->getEofPosition();
    echo $index . " - " . $current . "/" . $total . "\t\t" . round(($current / $total) * 100,2) . "% \n";
    $index++;


}

$ruend = getrusage();
echo "This process used " . rutime($ru, $rustart, "utime") .
    " ms for its computations\n";
echo "It spent " . rutime($ru, $rustart, "stime") .
    " ms in system calls\n";

