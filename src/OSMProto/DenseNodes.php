<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: proto/osmformat.proto

namespace OsmProto;

use Exception;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use OsmProto\GPBMetadata\Osmformat;

/**
 * Generated from protobuf message <code>OSMReader.DenseNodes</code>
 */
class DenseNodes extends Message
{
    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 id = 1 [packed = true];</code>
     */
    private $id;
    /**
     *repeated Info info = 4;
     *
     * Generated from protobuf field <code>.OSMReader.DenseInfo denseinfo = 5;</code>
     */
    private $denseinfo = null;
    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lat = 8 [packed = true];</code>
     */
    private $lat;
    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lon = 9 [packed = true];</code>
     */
    private $lon;
    /**
     * Special packing of keys and vals into one array. May be empty if all nodes in this block are tagless.
     *
     * Generated from protobuf field <code>repeated int32 keys_vals = 10 [packed = true];</code>
     */
    private $keys_vals;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     * @type int[]|string[]|RepeatedField $id
     *           DELTA coded
     * @type DenseInfo $denseinfo
     *          repeated Info info = 4;
     * @type int[]|string[]|RepeatedField $lat
     *           DELTA coded
     * @type int[]|string[]|RepeatedField $lon
     *           DELTA coded
     * @type int[]|RepeatedField $keys_vals
     *           Special packing of keys and vals into one array. May be empty if all nodes in this block are tagless.
     * }
     */
    public function __construct($data = NULL)
    {
        Osmformat::initOnce();
        parent::__construct($data);
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 id = 1 [packed = true];</code>
     * @return RepeatedField
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 id = 1 [packed = true];</code>
     * @param int[]|string[]|RepeatedField $var
     * @return $this
     * @throws Exception
     */
    public function setId($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::SINT64);
        $this->id = $arr;

        return $this;
    }

    /**
     *repeated Info info = 4;
     *
     * Generated from protobuf field <code>.OSMReader.DenseInfo denseinfo = 5;</code>
     * @return DenseInfo
     */
    public function getDenseinfo()
    {
        return $this->denseinfo;
    }

    /**
     *repeated Info info = 4;
     *
     * Generated from protobuf field <code>.OSMReader.DenseInfo denseinfo = 5;</code>
     * @param DenseInfo $var
     * @return $this
     * @throws Exception
     */
    public function setDenseinfo($var)
    {
        GPBUtil::checkMessage($var, DenseInfo::class);
        $this->denseinfo = $var;

        return $this;
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lat = 8 [packed = true];</code>
     * @return RepeatedField
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lat = 8 [packed = true];</code>
     * @param int[]|string[]|RepeatedField $var
     * @return $this
     * @throws Exception
     */
    public function setLat($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::SINT64);
        $this->lat = $arr;

        return $this;
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lon = 9 [packed = true];</code>
     * @return RepeatedField
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * DELTA coded
     *
     * Generated from protobuf field <code>repeated sint64 lon = 9 [packed = true];</code>
     * @param int[]|string[]|RepeatedField $var
     * @return $this
     * @throws Exception
     */
    public function setLon($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::SINT64);
        $this->lon = $arr;

        return $this;
    }

    /**
     * Special packing of keys and vals into one array. May be empty if all nodes in this block are tagless.
     *
     * Generated from protobuf field <code>repeated int32 keys_vals = 10 [packed = true];</code>
     * @return RepeatedField
     */
    public function getKeysVals()
    {
        return $this->keys_vals;
    }

    /**
     * Special packing of keys and vals into one array. May be empty if all nodes in this block are tagless.
     *
     * Generated from protobuf field <code>repeated int32 keys_vals = 10 [packed = true];</code>
     * @param int[]|RepeatedField $var
     * @return $this
     * @throws Exception
     */
    public function setKeysVals($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::INT32);
        $this->keys_vals = $arr;

        return $this;
    }

}

