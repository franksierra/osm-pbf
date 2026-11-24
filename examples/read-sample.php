<?php

declare(strict_types=1);

use OsmPbf\Reader;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Run composer install from the project root first.\n");
    exit(1);
}
require $autoload;

$path = $argv[1] ?? $root . '/data/test.osm.pbf';

$stream = @fopen($path, 'rb');
if ($stream === false) {
    fwrite(STDERR, "Cannot open PBF file: {$path}\n");
    exit(1);
}

$counts = ['node' => 0, 'way' => 0, 'relation' => 0];

try {
    $reader = new Reader($stream);
    $reader->readFileHeader();

    while ($reader->next() !== false) {
        $batch = $reader->getElements();
        $type = $batch['type'];

        // Replace this count with your own processing of $batch['data'].
        $counts[$type] = ($counts[$type] ?? 0) + count($batch['data']);
        unset($batch);
    }
} finally {
    fclose($stream);
}

printf("Read %s\n", basename($path));
foreach ($counts as $type => $count) {
    printf("%s: %d\n", $type, $count);
}
