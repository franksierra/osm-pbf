<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/19/2019
 * Time: 8:22 PM
 */

require_once __DIR__ . '/vendor/autoload.php';

use OSMReader\OSMReader;

$handle = fopen("data/ecuador-latest-internal.osm.pbf", "rb");
$osm = new OSMReader($handle);


$Header = $osm->readHeader();

echo "";