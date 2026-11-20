<?php

declare(strict_types=1);

namespace Unit;

use Google\Protobuf\Internal\GPBDecodeException;
use Google\Protobuf\Internal\Message;
use OsmPbf\Reader;
use OsmProto\Blob;
use OsmProto\BlobHeader;
use OsmProto\DenseInfo;
use OsmProto\DenseNodes;
use OsmProto\HeaderBBox;
use OsmProto\HeaderBlock;
use OsmProto\Info;
use OsmProto\PrimitiveBlock;
use OsmProto\PrimitiveGroup;
use OsmProto\Relation;
use OsmProto\Relation\MemberType;
use OsmProto\StringTable;
use OsmProto\Way;
use PHPUnit\Framework\TestCase;

final class ReaderTest extends TestCase
{
    /** @var resource|null */
    private $stream;

    protected function tearDown(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function testReadsHeaderMetadata(): void
    {
        $header = $this->openPbf([$this->wayBlock(100)])->readFileHeader();

        self::assertSame('PHPUnit fixture', $header->getWritingprogram());
        self::assertSame(['OsmSchema-V0.6', 'DenseNodes'], iterator_to_array($header->getRequiredFeatures()));
        self::assertSame(-80000000000, $header->getBbox()->getLeft());
        self::assertSame(-77000000000, $header->getBbox()->getRight());
        self::assertSame(1000000000, $header->getBbox()->getTop());
        self::assertSame(-3000000000, $header->getBbox()->getBottom());
        self::assertSame(1700000000, $header->getOsmosisReplicationTimestamp());
        self::assertSame(42, $header->getOsmosisReplicationSequenceNumber());
        self::assertSame('https://example.test/replication', $header->getOsmosisReplicationBaseUrl());
    }

    public function testReadsBlocksInOrderAndStopsAtEof(): void
    {
        $reader = $this->openPbf([$this->wayBlock(100), $this->wayBlock(200)]);
        $reader->readFileHeader();

        foreach ([100, 200] as $id) {
            self::assertInstanceOf(PrimitiveBlock::class, $reader->next());
            $elements = $reader->getElements();
            self::assertSame('way', $elements['type']);
            self::assertCount(1, $elements['data']);
            self::assertSame($id, $elements['data'][0]['id']);
        }

        self::assertFalse($reader->next());
        self::assertFalse($reader->next());
    }

    public function testReadsDataDirectlyAfterSkippingZeroBlocks(): void
    {
        $reader = $this->openPbf([$this->wayBlock(100)]);
        $reader->readFileHeader();

        self::assertTrue($reader->skipToBlock(0));
        self::assertInstanceOf(PrimitiveBlock::class, $reader->readData());
        self::assertSame(100, $reader->getElements()['data'][0]['id']);
        self::assertFalse($reader->next());
    }

    public function testSkipsOneDataBlockAfterReadingHeader(): void
    {
        $reader = $this->openPbf([$this->wayBlock(100), $this->wayBlock(200)]);
        $reader->readFileHeader();

        self::assertTrue($reader->skipToBlock(1));
        self::assertInstanceOf(PrimitiveBlock::class, $reader->next());
        self::assertSame(200, $reader->getElements()['data'][0]['id']);
        self::assertFalse($reader->next());
    }

    public function testDecodesDenseNodeDeltasTagsAndMetadata(): void
    {
        $dense = (new DenseNodes())
            ->setId([100, 7])
            ->setLat([10000000, -2000000])
            ->setLon([-20000000, 3000000])
            ->setKeysVals([1, 2, 0, 0])
            ->setDenseinfo((new DenseInfo())
                ->setVersion([1, 2])
                ->setTimestamp([1700000000, 60])
                ->setChangeset([10, 2])
                ->setUid([5, 2])
                ->setUserSid([3, 1]));
        $block = $this->primitiveBlock(
            (new PrimitiveGroup())->setDense($dense),
            ['', 'name', 'Example', 'first mapper', 'second mapper']
        )->setGranularity(100)->setLatOffset(200000000)->setLonOffset(-100000000);
        $reader = $this->openPbf([$block]);
        $reader->readFileHeader();
        self::assertInstanceOf(PrimitiveBlock::class, $reader->next());

        $elements = $reader->getElements();
        self::assertSame('node', $elements['type']);
        self::assertCount(2, $elements['data']);
        [$first, $second] = $elements['data'];
        self::assertSame(100, $first['id']);
        self::assertSame(107, $second['id']);
        self::assertEqualsWithDelta(1.2, $first['latitude'], 1e-9);
        self::assertEqualsWithDelta(-2.1, $first['longitude'], 1e-9);
        self::assertEqualsWithDelta(1.0, $second['latitude'], 1e-9);
        self::assertEqualsWithDelta(-1.8, $second['longitude'], 1e-9);
        self::assertSame([['key' => 'name', 'value' => 'Example']], $first['tags']);
        self::assertSame([], $second['tags']);
        self::assertSame([10, 12], array_column($elements['data'], 'changeset_id'));
        self::assertSame(['2023-11-14T22:13:20Z', '2023-11-14T22:14:20Z'], array_column($elements['data'], 'timestamp'));
        self::assertSame([1, 2], array_column($elements['data'], 'version'));
        self::assertSame([5, 7], array_column($elements['data'], 'uid'));
        self::assertSame(['first mapper', 'second mapper'], array_column($elements['data'], 'user'));
        self::assertSame([1, 1], array_column($elements['data'], 'visible'));
    }

    public function testDecodesOrderedWayReferencesTagsAndMetadata(): void
    {
        $reader = $this->openPbf([$this->wayBlock(100)]);
        $reader->readFileHeader();
        self::assertInstanceOf(PrimitiveBlock::class, $reader->next());

        $elements = $reader->getElements();
        self::assertSame('way', $elements['type']);
        $way = $elements['data'][0];
        self::assertSame([
            ['id' => 42, 'sequence' => 0],
            ['id' => 47, 'sequence' => 1],
            ['id' => 44, 'sequence' => 2],
        ], $way['nodes']);
        self::assertSame([['key' => 'highway', 'value' => 'path']], $way['tags']);
        self::assertSame(23, $way['changeset_id']);
        self::assertTrue($way['visible']);
        self::assertSame('2023-11-14T22:13:20Z', $way['timestamp']);
        self::assertSame(3, $way['version']);
        self::assertSame(8, $way['uid']);
        self::assertSame('mapper', $way['user']);
    }

    public function testDecodesOrderedRelationMembers(): void
    {
        $relation = (new Relation())
            ->setId(300)
            ->setInfo(new Info())
            ->setKeys([3])
            ->setVals([4])
            ->setMemids([50, 10, -3])
            ->setTypes([MemberType::NODE, MemberType::WAY, MemberType::RELATION])
            ->setRolesSid([1, 2, 0]);
        $reader = $this->openPbf([$this->primitiveBlock(
            (new PrimitiveGroup())->setRelations([$relation]),
            ['', 'stop', 'outer', 'type', 'route']
        )]);
        $reader->readFileHeader();
        self::assertInstanceOf(PrimitiveBlock::class, $reader->next());

        $elements = $reader->getElements();
        self::assertSame('relation', $elements['type']);
        self::assertSame(300, $elements['data'][0]['id']);
        self::assertSame([['key' => 'type', 'value' => 'route']], $elements['data'][0]['tags']);
        self::assertSame([
            ['member_type' => 'node', 'member_id' => 50, 'member_role' => 'stop', 'sequence' => 0],
            ['member_type' => 'way', 'member_id' => 60, 'member_role' => 'outer', 'sequence' => 1],
            ['member_type' => 'relation', 'member_id' => 57, 'member_role' => '', 'sequence' => 2],
        ], $elements['data'][0]['relations']);
    }

    public function testRejectsTruncatedDataBlockAfterReadingHeader(): void
    {
        $reader = $this->openPbf([$this->wayBlock(100)], truncateLastByte: true);
        self::assertInstanceOf(HeaderBlock::class, $reader->readFileHeader());

        $this->expectException(GPBDecodeException::class);
        $reader->next();
    }

    /** @param list<PrimitiveBlock> $blocks */
    private function openPbf(array $blocks, bool $truncateLastByte = false): Reader
    {
        $header = (new HeaderBlock())
            ->setRequiredFeatures(['OsmSchema-V0.6', 'DenseNodes'])
            ->setWritingprogram('PHPUnit fixture')
            ->setBbox((new HeaderBBox())
                ->setLeft(-80000000000)
                ->setRight(-77000000000)
                ->setTop(1000000000)
                ->setBottom(-3000000000))
            ->setOsmosisReplicationTimestamp(1700000000)
            ->setOsmosisReplicationSequenceNumber(42)
            ->setOsmosisReplicationBaseUrl('https://example.test/replication');

        $this->stream = fopen('php://memory', 'w+b');
        self::assertIsResource($this->stream);
        fwrite($this->stream, $this->frame('OSMHeader', $header));
        foreach ($blocks as $block) {
            fwrite($this->stream, $this->frame('OSMData', $block));
        }
        if ($truncateLastByte) {
            self::assertTrue(ftruncate($this->stream, ftell($this->stream) - 1));
        }
        rewind($this->stream);

        return new Reader($this->stream);
    }

    private function frame(string $type, Message $message): string
    {
        $payload = $message->serializeToString();
        $blob = (new Blob())
            ->setRawSize(strlen($payload))
            ->setZlibData(gzcompress($payload))
            ->serializeToString();
        $header = (new BlobHeader())
            ->setType($type)
            ->setDatasize(strlen($blob))
            ->serializeToString();

        return pack('N', strlen($header)) . $header . $blob;
    }

    private function wayBlock(int $id): PrimitiveBlock
    {
        $way = (new Way())
            ->setId($id)
            ->setInfo((new Info())
                ->setChangeset(23)
                ->setVisible(true)
                ->setTimestamp(1700000000)
                ->setVersion(3)
                ->setUid(8)
                ->setUserSid(3))
            ->setRefs([42, 5, -3])
            ->setKeys([1])
            ->setVals([2]);

        return $this->primitiveBlock((new PrimitiveGroup())->setWays([$way]), ['', 'highway', 'path', 'mapper']);
    }

    private function primitiveBlock(PrimitiveGroup $group, array $strings): PrimitiveBlock
    {
        return (new PrimitiveBlock())
            ->setStringtable((new StringTable())->setS($strings))
            ->setPrimitivegroup([$group]);
    }
}
