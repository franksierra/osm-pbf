<?php

declare(strict_types=1);

namespace Feature;

use OsmPbf\Reader;
use OsmProto\PrimitiveBlock;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('geofabrik')]
final class GeofabrikReaderTest extends TestCase
{
    public function testReadsTheTestExtractThroughEof(): void
    {
        $file = fopen(__DIR__ . '/../../data/test.osm.pbf', 'rb');
        self::assertIsResource($file);

        try {
            $reader = new Reader($file);
            $header = $reader->readFileHeader();
            self::assertContains('OsmSchema-V0.6', iterator_to_array($header->getRequiredFeatures()));
            $bbox = $header->getBbox();
            self::assertNotNull($bbox);
            self::assertLessThan($bbox->getRight(), $bbox->getLeft());
            self::assertLessThan($bbox->getTop(), $bbox->getBottom());

            $protobufCounts = ['node' => 0, 'way' => 0, 'relation' => 0];
            $elementCounts = $protobufCounts;
            while (($block = $reader->next()) !== false) {
                self::assertInstanceOf(PrimitiveBlock::class, $block);
                foreach ($block->getPrimitivegroup() as $group) {
                    $protobufCounts['node'] += count($group->getNodes()) + ($group->getDense()?->getId()->count() ?? 0);
                    $protobufCounts['way'] += count($group->getWays());
                    $protobufCounts['relation'] += count($group->getRelations());
                }
                $elements = $reader->getElements();
                self::assertArrayHasKey($elements['type'], $elementCounts);
                $elementCounts[$elements['type']] += count($elements['data']);
            }

            foreach ($protobufCounts as $type => $count) {
                self::assertGreaterThan(0, $count, "The fixture must contain $type records.");
            }
            self::assertSame($protobufCounts, $elementCounts);
        } finally {
            fclose($file);
        }
    }
}
