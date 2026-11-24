<?php

namespace OsmPbf;

use OsmProto\Blob;
use OsmProto\BlobHeader;
use OsmProto\HeaderBlock;
use OsmProto\PrimitiveBlock;
use OsmProto\Relation;
use OsmProto\Way;
use PhpBinaryReader\BinaryReader;

class Reader
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
        /** @var \OsmProto\PrimitiveGroup $primitive */
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
                "data" => $this->getRelations()
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
        /** @var \OsmProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];

        $dense = $primitive->getDense();
        $dense_info = $dense->getDenseinfo();
        $ids = $dense->getId();
        $latitudes = $dense->getLat();
        $longitudes = $dense->getLon();
        $changesets = $dense_info->getChangeset();
        $timestamps = $dense_info->getTimestamp();
        $uids = $dense_info->getUid();
        $user_sids = $dense_info->getUserSid();
        $versions = $dense_info->getVersion();
        $visibility = $dense_info->getVisible();
        $keys_vals = $dense->getKeysVals();
        $keys_vals_count = $keys_vals->count();
        $strings = $data->getStringtable()->getS();
        $lat_offset = $data->getLatOffset();
        $lon_offset = $data->getLonOffset();
        $granularity = $data->getGranularity();

        $id = $latitude = $longitude = $changeset = $timestamp = $uid = $user_sid = 0;
        $total = $ids->count();
        $nodes = [];
        for ($i = 0, $ikv = 0; $i < $total; $i++) {
            $id += $ids[$i];
            $latitude += $latitudes[$i];
            $longitude += $longitudes[$i];
            $changeset += $changesets[$i];
            $timestamp += $timestamps[$i];
            $uid += $uids[$i];
            $user_sid += $user_sids[$i];

            $tags = [];
            while ($ikv < $keys_vals_count && $keys_vals[$ikv] != 0) {
                $k = $strings[(int)$keys_vals[$ikv++]];
                $v = $strings[(int)$keys_vals[$ikv++]];
                $tags[] = array("key" => $k, "value" => $v);
            }
            if ($ikv < $keys_vals_count) {
                $ikv++;
            }

            $nodes[] = array(
                "id" => $id,
                "latitude" => 0.000000001 * ($lat_offset + ($granularity * $latitude)),
                "longitude" => 0.000000001 * ($lon_offset + ($granularity * $longitude)),
                "changeset_id" => $changeset,
                "visible" => $visibility[$i] ?? 1,
                "timestamp" => gmdate("Y-m-d\TH:i:s\Z", $timestamp),
                "version" => $versions[$i],
                "uid" => $uid,
                "user" => $strings[(int)$user_sid],
                "tags" => $tags,
                "nodes" => array(),
                "relations" => array(),
            );
        }

        return $nodes;

    }

    public function getWays()
    {
        $data = $this->current_primitive;
        /** @var \OsmProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];
        $ways = $primitive->getWays();
        $strings = $data->getStringtable()->getS();
        $_ways = [];
        /** @var Way $way */
        foreach ($ways as $way) {
            $info = $way->getInfo();
            $_way = array(
                "id" => $way->getId(),
                "changeset_id" => $info->getChangeset(),
                "visible" => $info->getVisible(),
                "timestamp" => gmdate("Y-m-d\TH:i:s\Z", $info->getTimestamp()),
                "version" => $info->getVersion(),
                "uid" => $info->getUid(),
                "user" => $strings[$info->getUserSid()],
                "tags" => array(),
                "nodes" => array(),
                "relations" => array()
            );
            $node_id = 0;
            $refs = $way->getRefs();
            $ref_count = $refs->count();
            for ($i = 0; $i < $ref_count; $i++) {
                $node_id += $refs[$i];
                $node = array(
                    "id" => $node_id,
                    "sequence" => $i
                );
                $_way["nodes"][] = $node;
            }
            $keys = $way->getKeys();
            $values = $way->getVals();
            $key_count = $keys->count();
            for ($i = 0; $i < $key_count; $i++) {
                $k = $strings[(int)$keys[$i]];
                $v = $strings[(int)$values[$i]];
                $_way["tags"][] = array("key" => $k, "value" => $v);
            }
            $_ways[] = $_way;
        }
        return $_ways;
    }

    private function getRelations()
    {
        $data = $this->current_primitive;
        /** @var \OsmProto\PrimitiveGroup $primitive */
        $primitive = $data->getPrimitivegroup()[0];
        $relations = $primitive->getRelations();
        $strings = $data->getStringtable()->getS();
        $_relations = [];

        /** @var Relation $relation */
        foreach ($relations as $relation) {
            $info = $relation->getInfo();
            $_relation = array(
                "id" => $relation->getId(),
                "changeset_id" => $info->getChangeset(),
                "visible" => $info->getVisible(),
                "timestamp" => gmdate("Y-m-d\TH:i:s\Z", $info->getTimestamp()),
                "version" => $info->getVersion(),
                "uid" => $info->getUid(),
                "user" => $strings[$info->getUserSid()],
                "tags" => array(),
                "nodes" => array(),
                "relations" => array()
            );
            $member_id = 0;
            $keys = $relation->getKeys();
            $values = $relation->getVals();
            $key_count = $keys->count();
            for ($i = 0; $i < $key_count; $i++) {
                $k = $strings[(int)$keys[$i]];
                $v = $strings[(int)$values[$i]];
                $_relation["tags"][] = array("key" => $k, "value" => $v);
            }

            $member_ids = $relation->getMemids();
            $types = $relation->getTypes();
            $roles = $relation->getRolesSid();
            $member_count = $member_ids->count();
            for ($i = 0; $i < $member_count; $i++) {
                $member_id += $member_ids[$i];
                switch ($types[$i]) {
                    case Relation\MemberType::NODE:
                        $type = "node";
                        break;
                    case Relation\MemberType::RELATION:
                        $type = "relation";
                        break;
                    case Relation\MemberType::WAY:
                        $type = "way";
                        break;
                    default:
                        $type = "";
                }
                $member = array(
                    "member_type" => $type,
                    "member_id" => $member_id,
                    "member_role" => $strings[(int)$roles[$i]],
                    "sequence" => $i
                );
                $_relation["relations"][] = $member;
            }
            $_relations[] = $_relation;
        }
        return $_relations;

    }

}