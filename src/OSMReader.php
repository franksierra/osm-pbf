<?php
/**
 * Created by PhpStorm.
 * User: sierraf
 * Date: 2/19/2019
 * Time: 5:14 PM
 */

namespace OSMReader;

use OSMProto\Blob;
use OSMProto\BlobHeader;
use OSMProto\HeaderBlock;
use OSMProto\PrimitiveBlock;
use OSMProto\Way;
use PhpBinaryReader\BinaryReader;

class OSMReader
{
    /** @var PrimitiveBlock $current_primitive */
    private $current_primitive;

    private $binary_reader;

    public function __construct($pbfdata)
    {
        $this->binary_reader = new BinaryReader($pbfdata, "big");
    }

    public function getReader()
    {
        return $this->binary_reader;
    }

    public function readFileHeader()
    {
        $size = $this->binary_reader->readUInt32();
        $header_data = $this->binary_reader->readFromHandle($size);
        $header = new BlobHeader();
        $header->mergeFromString($header_data);
        $blob_data = $this->binary_reader->readFromHandle($header->getDatasize());
        $blob = new Blob();
        $blob->mergeFromString($blob_data);

        $blob_uncompressed = zlib_decode($blob->getZlibData());

        $header_block = new HeaderBlock();
        $header_block->mergeFromString($blob_uncompressed);

        return $header_block;
    }

    public function readData($skip_blob = false)
    {
        /**
         * OSMData
         */
        $size = $this->binary_reader->readUInt32();
        $header_data = $this->binary_reader->readFromHandle($size);
        $header = new BlobHeader();
        $header->mergeFromString($header_data);
        $blob_data = $this->binary_reader->readFromHandle($header->getDatasize());

        /** @var PrimitiveBlock $primitive_block */
        $primitive_block = null;
        if (!$skip_blob) {
            $blob = new Blob();
            $blob->mergeFromString($blob_data);
            $blob_uncompressed = zlib_decode($blob->getZlibData());

            $primitive_block = new PrimitiveBlock();
            $primitive_block->mergeFromString($blob_uncompressed);

            $this->current_primitive = $primitive_block;
        }

        return $primitive_block;
    }

    public function skipToBlock($end)
    {
        for ($start = 0; $start < $end; $start++) {
            $this->readData(true);
        }
        return true;
    }

    public function next()
    {
        if ($this->binary_reader->getPosition() < $this->binary_reader->getEofPosition()) {
            return $this->readData();
        }
        return false;
    }

    public function getElements()
    {
        $data = $this->current_primitive;
        /** @var \OSMProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];

        $elements = array(
            "type" => "",
            "data" => array()
        );
        if ($primitive->getNodes()->count() > 0) {
            $elements = array(
                "type" => "node",
                "data" => array()
            );
        } elseif ($primitive->getWays()->count() > 0) {
            $elements = array(
                "type" => "way",
                "data" => $this->getWays()
            );
        } elseif ($primitive->getRelations()->count() > 0) {
            $elements = array(
                "type" => "relation",
                "data" => array()
            );
        } elseif ($primitive->getChangesets()->count() > 0) {
            $elements = array(
                "type" => "changeset",
                "data" => array()
            );
        } elseif ($primitive->getDense()->getId()->count() > 0) {
            $elements = array(
                "type" => "node",
                "data" => $this->getDenseNodes()
            );
        }
        return $elements;
    }

    public function getDenseNodes()
    {
        $data = $this->current_primitive;
        /** @var \OSMProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];

        $dense = $primitive->getDense();
        $dense_info = $dense->getDenseinfo();
        $total = $dense->getId()->count();
        $dense_node = array(
            "id" => 0,
            "latitude" => 0,
            "longitude" => 0,
            "changeset_id" => 0,
            "timestamp" => 0,
            "user" => 0,
            "tags" => array(),
        );
        $nodes = [];

        for ($i = 0, $ikv = 0; $i < $total; $i++) {
            $dense_node["id"] += $dense->getId()[$i];
            $dense_node["latitude"] += $dense->getLat()[$i];
            $dense_node["longitude"] += $dense->getLon()[$i];
            $dense_node["changeset_id"] += $dense_info->getChangeset()[$i];
            $dense_node["timestamp"] += $dense_info->getTimestamp()[$i];
            $dense_node["user"] += $dense_info->getUserSid()[$i];

            if ($dense->getKeysVals()[$ikv] != 0) {
                do {
                    $k = $data->getStringtable()->getS()[(int)$dense->getKeysVals()[$ikv]];
                    $v = $data->getStringtable()->getS()[(int)$dense->getKeysVals()[++$ikv]];
                    $dense_node["tags"][$k] = $v;
                    $ikv++;
                } while ($dense->getKeysVals()[$ikv] != 0);
            } else {
                $ikv++;
            }

            $nodes[] = array(
                "id" => $dense_node["id"],
                "latitude" => 0.000000001 * ($data->getLatOffset() + ($data->getGranularity() * $dense_node["latitude"])),
                "longitude" => 0.000000001 * ($data->getLonOffset() + ($data->getGranularity() * $dense_node["longitude"])),
                "changeset_id" => $dense_node["changeset_id"],
                "visible" => $dense_info->getVisible()[$i] ?? 1,
                "timestamp" => $dense_node["timestamp"] * $data->getDateGranularity(),
                "version" => $dense_info->getVersion()[$i],
                "user" => $data->getStringtable()->getS()[(int)$dense_node["user"]],
                "tags" => $dense_node["tags"],
            );

        }

        return $nodes;

    }

    public function getWays()
    {
        $data = $this->current_primitive;
        /** @var \OSMProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];
        $ways = $primitive->getWays();

        $_way = array(
            "id" => 0,
            "changeset_id" => 0,
            "timestamp" => 0,
            "user" => 0,
            "tags" => array(),
            "nodes" => array(),
        );
        $_ways = [];
        /** @var Way $way */
        foreach ($ways as $way) {
            $_way["id"] = $way->getId();
            $_way["changeset_id"] = $way->getInfo()->getChangeset();
            $_way["timestamp"] = $way->getInfo()->getTimestamp() * $data->getDateGranularity();
            $_way["user"] = $data->getStringtable()->getS()[$way->getInfo()->getUserSid()];

            $node = 0;
            for ($i = 0; $i < $way->getRefs()->count(); $i++) {
                $node += $way->getRefs()[$i];
                $_way["nodes"][] = $node;
            }
            for ($i = 0; $i < $way->getKeys()->count(); $i++) {
                $k = $data->getStringtable()->getS()[(int)$way->getKeys()[$i]];
                $v = $data->getStringtable()->getS()[(int)$way->getKeys()[$i]];
                $_way["tags"][$k] = $v;
            }
            $_ways[] = $_way;
        }
        return $_ways;
    }

}